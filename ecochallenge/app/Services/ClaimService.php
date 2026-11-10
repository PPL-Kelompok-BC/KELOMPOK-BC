<?php

namespace App\Services;

use App\Exceptions\ClaimAlreadyReviewedException;
use App\Models\Claim;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Logika bisnis klaim yang menyangkut poin (PBI-19).
 */
class ClaimService
{
    public function __construct(private readonly LeaderboardService $leaderboard) {}

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
