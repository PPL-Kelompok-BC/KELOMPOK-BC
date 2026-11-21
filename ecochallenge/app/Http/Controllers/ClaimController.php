<?php

namespace App\Http\Controllers;

use App\Exceptions\ClaimAlreadyReviewedException;
use App\Exceptions\InvalidClaimPhotoException;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use App\Services\ClaimPhotoService;
use App\Services\ClaimService;
use App\Services\LeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ClaimController extends Controller
{
    public function __construct(
        private readonly ClaimPhotoService $photos,
        private readonly ClaimService $claims,
    ) {}

    /** PBI-15: formulir unggah bukti foto. Challenge nonaktif menampilkan alasan, bukan 404 (PBI-11). */
    public function create(Challenge $challenge): View
    {
        return view('claims.create', compact('challenge'));
    }

    /** PBI-15/PBI-16: simpan klaim baru dengan verifikasi berlapis. */
    public function store(StoreClaimRequest $request, Challenge $challenge): RedirectResponse
    {
        $user = $request->user();

        // Proposal Tabel 2.3 butir (a) dan PBI-11 eksepsi.
        if (! $challenge->is_active) {
            return back()->withErrors(['photo' => 'Challenge ini sudah tidak aktif.']);
        }

        // Proposal Tabel 2.3 butir (c): satu klaim per challenge per hari (WIB).
        if ($this->alreadyClaimedToday($user, $challenge)) {
            return back()->withErrors(['photo' => 'Kamu sudah mengklaim challenge ini hari ini.']);
        }

        try {
            $photo = $this->photos->store($user->getKey(), $request->file('photo'));
        } catch (InvalidClaimPhotoException $exception) {
            return back()->withErrors(['photo' => $exception->getMessage()]);
        }

        // UC-06 langkah 7-8: klaim yang lolos verifikasi langsung diterima, poin dan emisi
        // ditambahkan dalam satu transaksi, lalu leaderboard diperbarui (PBI-19, PBI-20).
        try {
            $claim = $this->claims->submit($user, $challenge, $photo, $request->note);
        } catch (Throwable $exception) {
            // UC-06 alur 7a: transaksi gagal, foto yang sudah tersimpan dihapus.
            $this->photos->delete($photo['path']);
            report($exception);

            return back()->withErrors(['photo' => 'Klaim gagal disimpan. Silakan coba lagi.']);
        }

        return redirect()
            ->route('claims.show', $claim)
            ->with('status', "Klaim diterima. +{$claim->points_awarded} poin ditambahkan.");
    }

    /** Riwayat klaim milik pengguna. */
    public function index(Request $request): View
    {
        $claims = Claim::where('user_id', $request->user()->id)
            ->with('challenge')
            ->latest()
            ->get();

        return view('claims.index', compact('claims'));
    }

    /** UC-06 langkah 9 (Hasil Klaim) dan PBI-18: detail klaim beserta alasan penolakan. */
    public function show(Request $request, Claim $claim, LeaderboardService $leaderboard): View
    {
        $this->authorizeView($claim, $request->user());

        $claim->load(['challenge', 'user']);

        // Ringkasan terbaru milik pemilik klaim: total poin, peringkat, dan total estimasi emisi.
        $result = null;
        if ($claim->status === Claim::STATUS_APPROVED) {
            $owner = $claim->user;
            $result = [
                'total_points' => (int) $owner->total_points,
                'rank' => $leaderboard->rankOf($owner),
                'total_co2e_gram' => (int) Claim::where('user_id', $owner->id)
                    ->where('status', Claim::STATUS_APPROVED)
                    ->sum('co2e_awarded_gram'),
            ];
        }

        return view('claims.show', compact('claim', 'result'));
    }

    /** PBI-18: kirim ulang bukti untuk klaim yang ditolak. */
    public function resubmit(StoreClaimRequest $request, Claim $claim): RedirectResponse
    {
        $this->authorizeView($claim, $request->user());

        if ($claim->status !== Claim::STATUS_REJECTED) {
            return back()->withErrors(['photo' => 'Hanya bukti yang ditolak yang bisa dikirim ulang.']);
        }

        try {
            $photo = $this->photos->store($claim->user_id, $request->file('photo'));
        } catch (InvalidClaimPhotoException $exception) {
            return back()->withErrors(['photo' => $exception->getMessage()]);
        }

        $oldPath = $claim->proof_path;

        try {
            $claim = $this->claims->resubmit($claim, $photo, $request->note);
        } catch (Throwable $exception) {
            $this->photos->delete($photo['path']);

            if (! $exception instanceof ClaimAlreadyReviewedException) {
                report($exception);
            }

            return back()->withErrors(['photo' => $exception instanceof ClaimAlreadyReviewedException
                ? $exception->getMessage()
                : 'Klaim gagal disimpan. Silakan coba lagi.']);
        }

        $this->photos->delete($oldPath);

        return redirect()
            ->route('claims.show', $claim)
            ->with('status', "Bukti baru diterima. +{$claim->points_awarded} poin ditambahkan.");
    }

    /** PBI-17: streaming foto privat (hanya pemilik atau admin); mahasiswa lain menerima 403. */
    public function photo(Request $request, Claim $claim): StreamedResponse
    {
        $this->authorizeView($claim, $request->user(), 403);

        $path = $claim->proof_path;
        $disk = Storage::disk(ClaimPhotoService::DISK);

        abort_if(! $path || ! $disk->exists($path), 404);

        return $disk->response($path);
    }

    private function authorizeView(Claim $claim, ?User $user, int $status = 404): void
    {
        abort_unless(
            $user && ($claim->user_id === $user->id || $user->role === User::ROLE_ADMIN),
            $status
        );
    }

    /** PBI-16: satu klaim per challenge per hari. */
    private function alreadyClaimedToday(User $user, Challenge $challenge): bool
    {
        return Claim::where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            // whereDate: SQLite menyimpan kolom date sebagai "Y-m-d 00:00:00", jadi perbandingan
            // string biasa tidak pernah cocok dan klaim kedua baru tertahan unique index (error 500).
            ->whereDate('claim_date', today()->toDateString())
            ->exists();
    }
}
