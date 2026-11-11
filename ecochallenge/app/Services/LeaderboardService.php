<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Leaderboard mahasiswa (PBI-20 s.d. PBI-22).
 *
 * Urutan: total poin terbanyak lebih dulu; bila poin sama, mengikuti username.
 * Akun admin dan akun yang dinonaktifkan tidak ikut dihitung.
 */
class LeaderboardService
{
    /** Jumlah baris teratas yang ditampilkan. */
    public const TOP_LIMIT = 50;

    public const CACHE_KEY = 'leaderboard.top';

    /**
     * Umur cache (detik). Dibuat 2 detik supaya data paling lama tertinggal 2 detik
     * walaupun pembaruan cache gagal (NFR-01), dan refresh() membuatnya langsung segar.
     */
    public const CACHE_TTL_SECONDS = 2;

    /**
     * Daftar peringkat teratas.
     *
     * @return list<array{rank:int, id:int, username:string, total_points:int}>
     */
    public function top(int $limit = self::TOP_LIMIT): array
    {
        try {
            return Cache::remember(
                self::CACHE_KEY.'.'.$limit,
                self::CACHE_TTL_SECONDS,
                fn () => $this->computeTop($limit)
            );
        } catch (Throwable $e) {
            // Cache gagal dibaca atau ditulis: hitung ulang langsung dari basis data.
            Log::warning('Cache leaderboard gagal, memakai basis data.', ['error' => $e->getMessage()]);

            return $this->computeTop($limit);
        }
    }

    /**
     * Peringkat satu pengguna, dihitung dari basis data (tidak dari cache) agar selalu
     * sesuai dengan poin terbaru. Null bila pengguna bukan mahasiswa aktif.
     */
    public function rankOf(User $user): ?int
    {
        if (! $this->isRanked($user)) {
            return null;
        }

        $ahead = $this->rankedQuery()
            ->where(function ($query) use ($user) {
                $query->where('total_points', '>', $user->total_points)
                    ->orWhere(function ($tie) use ($user) {
                        $tie->where('total_points', $user->total_points)
                            ->where('username', '<', $user->username);
                    });
            })
            ->count();

        return $ahead + 1;
    }

    /**
     * Segarkan cache setelah poin berubah (dipanggil dari alur penambahan poin, PBI-19).
     * Kegagalan hanya dicatat: pembacaan berikutnya tetap benar lewat umur cache atau basis data.
     */
    public function refresh(): void
    {
        try {
            Cache::forget(self::CACHE_KEY.'.'.self::TOP_LIMIT);
            $this->top(self::TOP_LIMIT);
        } catch (Throwable $e) {
            Log::warning('Pembaruan cache leaderboard gagal.', ['error' => $e->getMessage()]);
        }
    }

    /** Jumlah mahasiswa aktif yang ikut peringkat. */
    public function participantCount(): int
    {
        return $this->rankedQuery()->count();
    }

    public function isRanked(User $user): bool
    {
        return $user->role === User::ROLE_MAHASISWA && (bool) $user->is_active;
    }

    /** @return \Illuminate\Database\Eloquent\Builder<User> */
    private function rankedQuery()
    {
        return User::query()
            ->where('role', User::ROLE_MAHASISWA)
            ->where('is_active', true);
    }

    /**
     * @return list<array{rank:int, id:int, username:string, total_points:int}>
     */
    private function computeTop(int $limit): array
    {
        return $this->rankedQuery()
            ->orderByDesc('total_points')
            ->orderBy('username')
            ->limit($limit)
            ->get(['id', 'username', 'total_points'])
            ->values()
            ->map(fn (User $user, int $index) => [
                'rank' => $index + 1,
                'id' => $user->id,
                'username' => $user->username,
                'total_points' => (int) $user->total_points,
            ])
            ->all();
    }
}
