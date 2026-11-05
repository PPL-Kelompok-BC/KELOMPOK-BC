<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidClaimPhotoException;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use App\Services\ClaimPhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClaimController extends Controller
{
    public function __construct(private readonly ClaimPhotoService $photos) {}

    /** PBI-15: formulir unggah bukti foto. */
    public function create(Challenge $challenge): View
    {
        abort_unless($challenge->is_active, 404);

        return view('claims.create', compact('challenge'));
    }

    /** PBI-15/PBI-16: simpan klaim baru dengan verifikasi berlapis. */
    public function store(StoreClaimRequest $request, Challenge $challenge): RedirectResponse
    {
        abort_unless($challenge->is_active, 404);

        $user = $request->user();

        if ($this->alreadyClaimedToday($user, $challenge)) {
            return back()->withErrors(['photo' => 'Anda sudah mengirim bukti untuk challenge ini hari ini.']);
        }

        try {
            $photo = $this->photos->store($user->getKey(), $request->file('photo'));
        } catch (InvalidClaimPhotoException $exception) {
            return back()->withErrors(['photo' => $exception->getMessage()]);
        }

        $claim = Claim::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'proof_path' => $photo['path'],
            'photo_hash' => $photo['hash'],
            'photo_taken_at' => $photo['taken_at'],
            'needs_review' => true,
            'claim_date' => today()->toDateString(),
            'status' => Claim::STATUS_PENDING,
            'note' => $request->note,
        ]);

        return redirect()
            ->route('claims.show', $claim)
            ->with('status', 'Bukti berhasil dikirim dan menunggu verifikasi.');
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

    /** PBI-18: detail klaim beserta alasan penolakan (bila ditolak). */
    public function show(Request $request, Claim $claim): View
    {
        $this->authorizeView($claim, $request->user());

        $claim->load('challenge');

        return view('claims.show', compact('claim'));
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

        $this->photos->delete($claim->proof_path);

        $claim->update([
            'proof_path' => $photo['path'],
            'photo_hash' => $photo['hash'],
            'photo_taken_at' => $photo['taken_at'],
            'needs_review' => true,
            'status' => Claim::STATUS_PENDING,
            'cancel_reason' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'note' => $request->note,
        ]);

        return redirect()
            ->route('claims.show', $claim)
            ->with('status', 'Bukti baru berhasil dikirim ulang dan menunggu verifikasi.');
    }

    /** PBI-17: streaming foto privat (hanya pemilik atau admin). */
    public function photo(Request $request, Claim $claim): StreamedResponse
    {
        $this->authorizeView($claim, $request->user());

        $path = $claim->proof_path;
        $disk = Storage::disk(ClaimPhotoService::DISK);

        abort_if(! $path || ! $disk->exists($path), 404);

        return $disk->response($path);
    }

    private function authorizeView(Claim $claim, ?User $user): void
    {
        abort_unless(
            $user && ($claim->user_id === $user->id || $user->role === User::ROLE_ADMIN),
            404
        );
    }

    /** PBI-16: satu klaim per challenge per hari. */
    private function alreadyClaimedToday(User $user, Challenge $challenge): bool
    {
        return Claim::where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->where('claim_date', today()->toDateString())
            ->exists();
    }
}
