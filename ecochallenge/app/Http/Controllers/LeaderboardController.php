<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    /** PBI-20 s.d. PBI-22: leaderboard keseluruhan berdasarkan total poin. */
    public function index(LeaderboardService $leaderboard): View
    {
        $user = auth()->user();

        $rows = $leaderboard->top();
        $myRank = $leaderboard->rankOf($user);
        $inTop = collect($rows)->contains('id', $user->id);

        // Pengguna di luar 50 besar tetap melihat baris posisinya sendiri di bawah daftar (PBI-21).
        $myRow = ($myRank !== null && ! $inTop)
            ? ['rank' => $myRank, 'id' => $user->id, 'username' => $user->username, 'total_points' => (int) $user->total_points]
            : null;

        return view('leaderboard.index', [
            'rows' => $rows,
            'myRow' => $myRow,
            'myId' => $user->id,
            'myRank' => $myRank,
            'limit' => LeaderboardService::TOP_LIMIT,
        ]);
    }
}
