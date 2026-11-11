<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Dashboard mahasiswa (ringkasan sementara; ringkasan lengkap menyusul di PBI-29 s.d. PBI-31). */
    public function index(Request $request, LeaderboardService $leaderboard): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $counts = Claim::query()
            ->where('user_id', $user->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard', [
            'user' => $user,
            'greeting' => $this->greeting(),
            'points' => (int) $user->total_points,
            'rank' => $leaderboard->rankOf($user),
            'participants' => $leaderboard->participantCount(),
            'pending' => (int) ($counts[Claim::STATUS_PENDING] ?? 0),
            'approved' => (int) ($counts[Claim::STATUS_APPROVED] ?? 0),
            'recentClaims' => Claim::with('challenge')->where('user_id', $user->id)->latest()->limit(4)->get(),
            'featured' => Challenge::active()->with('category')->orderByDesc('points')->limit(3)->get(),
        ]);
    }

    /** Dashboard admin: ringkasan angka utama. */
    public function admin(): View
    {
        return view('admin.dashboard', [
            'students' => User::where('role', User::ROLE_MAHASISWA)->where('is_active', true)->count(),
            'challenges' => Challenge::active()->count(),
            'pending' => Claim::where('status', Claim::STATUS_PENDING)->count(),
            'approved' => Claim::where('status', Claim::STATUS_APPROVED)->count(),
        ]);
    }

    private function greeting(): string
    {
        return match (true) {
            now()->hour < 11 => 'Selamat pagi',
            now()->hour < 15 => 'Selamat siang',
            now()->hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
