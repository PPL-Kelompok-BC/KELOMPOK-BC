<?php

namespace App\Services;

use App\Exceptions\ClaimAlreadyReviewedException;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Logika bisnis klaim yang menyangkut poin (PBI-19).
 *
 * Sesuai proposal UC-06 Klaim Challenge: klaim yang lolos verifikasi berlapis langsung
 * DITERIMA, dan dalam satu transaksi sistem menyimpan klaim, menambah poin, serta mencatat
 * estimasi emisi; setelah itu cache leaderboard diperbarui. Admin tidak menyetujui klaim,
 * tetapi meninjau dan bisa membatalkannya kemudian (UC-13, PBI-36 s.d. PBI-38).
 */
class ClaimService
{
    public function __construct(private readonly LeaderboardService $leaderboard) {}

    /**
     * UC-06 langkah 7 dan 8: simpan klaim yang sudah lolos verifikasi sebagai diterima,
     * tambah poin challenge ke total poin mahasiswa, dan catat estimasi emisi dalam SATU
     * transaksi. Bila transaksi gagal, tidak ada perubahan yang tersimpan (UC-06 alur 7a).
     *
     * @param  array{path:string, hash:string, taken_at:mixed}  $photo  hasil ClaimPhotoService::store
     */
    public function submit(User $user, Challenge $challenge, array $photo, ?string $note = null): Claim
    {
        $claim = DB::transaction(function () use ($user, $challenge, $photo, $note) {
            $claim = Claim::create([
                'user_id' => $user->getKey(),
                'challenge_id' => $challenge->getKey(),
                'proof_path' => $photo['path'],
                'photo_hash' => $photo['hash'],
                'photo_taken_at' => $photo['taken_at'],
                // UC-06 alur 5b: foto tanpa EXIF tetap diterima, tetapi diberi tanda prioritas tinjau.
                'needs_review' => $photo['taken_at'] === null,
                'claim_date' => today()->toDateString(),
                'status' => Claim::STATUS_APPROVED,
                'points_awarded' => $challenge->points,
                'co2e_awarded_gram' => $challenge->estimated_co2e_gram,
                'note' => $note,
            ]);

            // Penambahan atomik di basis data, bukan baca-tambah-tulis di PHP.
            User::query()->whereKey($user->getKey())->increment('total_points', $challenge->points);

            return $claim;
        });

        // Di luar transaksi: kegagalan menyegarkan cache hanya dicatat, tidak membatalkan poin.
        $this->leaderboard->refresh();

        return $claim;
    }

    /**
     * PBI-18: bukti baru untuk klaim yang sebelumnya dibatalkan. Bila lolos verifikasi, klaim
     * kembali diterima dan poinnya ditambahkan lagi dalam satu transaksi.
     *
     * @param  array{path:string, hash:string, taken_at:mixed}  $photo
     *
     * @throws ClaimAlreadyReviewedException bila klaim tidak berstatus dibatalkan/ditolak
     */
    public function resubmit(Claim $claim, array $photo, ?string $note = null): Claim
    {
        $accepted = DB::transaction(function () use ($claim, $photo, $note) {
            $locked = Claim::query()->with('challenge')->whereKey($claim->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== Claim::STATUS_REJECTED) {
                throw new ClaimAlreadyReviewedException('Hanya bukti yang ditolak yang bisa dikirim ulang.');
            }

            $challenge = $locked->challenge;

            $locked->update([
                'proof_path' => $photo['path'],
                'photo_hash' => $photo['hash'],
                'photo_taken_at' => $photo['taken_at'],
                'needs_review' => $photo['taken_at'] === null,
                'status' => Claim::STATUS_APPROVED,
                'points_awarded' => $challenge->points,
                'co2e_awarded_gram' => $challenge->estimated_co2e_gram,
                'cancel_reason' => null,
                'reviewed_at' => null,
                'reviewed_by' => null,
                'note' => $note,
            ]);

            User::query()->whereKey($locked->user_id)->increment('total_points', $challenge->points);

            return $locked;
        });

        $this->leaderboard->refresh();

        return $accepted;
    }

    /**
     * Setujui klaim yang masih menunggu dan tambahkan poin challenge ke total poin
     * mahasiswa dalam SATU transaksi dengan penyimpanan klaim.
     *
     * - Bila penyimpanan klaim gagal, transaksi dibatalkan sehingga total poin tidak berubah.
     * - Klaim yang sudah direview tidak bisa disetujui lagi, jadi poin tidak pernah ganda.
     * - Poin yang diberikan dicatat di claims.points_awarded agar bisa dikoreksi bila
     *   klaim dibatalkan kemudian (PBI-29).
     * - Setelah transaksi berhasil, cache leaderboard disegarkan (PBI-22).
     *
     * @throws ClaimAlreadyReviewedException bila klaim bukan berstatus pending
     */
    public function approve(Claim $claim, ?User $reviewer = null): Claim
    {
        $approved = DB::transaction(function () use ($claim, $reviewer) {
            // Kunci baris klaim supaya dua persetujuan bersamaan tidak sama-sama lolos.
            $locked = Claim::query()
                ->with('challenge')
                ->whereKey($claim->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== Claim::STATUS_PENDING) {
                throw new ClaimAlreadyReviewedException('Klaim ini sudah direview.');
            }

            $challenge = $locked->challenge;

            $locked->update([
                'status' => Claim::STATUS_APPROVED,
                'points_awarded' => $challenge->points,
                'co2e_awarded_gram' => $challenge->estimated_co2e_gram,
                'cancel_reason' => null,
                'needs_review' => false,
                'reviewed_by' => $reviewer?->getKey(),
                'reviewed_at' => now(),
            ]);

            // Penambahan atomik di basis data, bukan baca-tambah-tulis di PHP.
            User::query()->whereKey($locked->user_id)->increment('total_points', $challenge->points);

            return $locked;
        });

        // Di luar transaksi: kegagalan menyegarkan cache hanya dicatat, tidak membatalkan poin.
        $this->leaderboard->refresh();

        return $approved;
    }
}
