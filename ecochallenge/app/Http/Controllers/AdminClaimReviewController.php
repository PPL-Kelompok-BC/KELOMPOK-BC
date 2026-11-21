<?php

namespace App\Http\Controllers;

use App\Exceptions\ClaimAlreadyReviewedException;
use App\Models\Claim;
use App\Services\ClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * PBI-19 (SCRUM-31): admin menerima klaim sehingga poin bertambah otomatis.
 *
 * Halaman ini menyambungkan tombol "Setujui" di web ke ClaimService::approve, yang menambah
 * users.total_points di basis data dan menyegarkan leaderboard (PBI-20). Pembatalan klaim
 * beserta koreksi poin menjadi bagian PBI-36 s.d. PBI-38 (Tinjau dan Batalkan Klaim).
 */
class AdminClaimReviewController extends Controller
{
    /** Klaim yang masih menunggu tinjauan, yang paling lama menunggu di atas. */
    public function index(): View
    {
        $claims = Claim::query()
            ->where('status', Claim::STATUS_PENDING)
            ->with(['user:id,name,username,total_points', 'challenge:id,title,points'])
            ->oldest()
            ->get();

        return view('admin.claims.index', compact('claims'));
    }

    /** Terima klaim: status menjadi disetujui dan poin challenge ditambahkan ke mahasiswa. */
    public function approve(Request $request, Claim $claim, ClaimService $service): RedirectResponse
    {
        try {
            $approved = $service->approve($claim, $request->user());
        } catch (ClaimAlreadyReviewedException $exception) {
            return redirect()->route('admin.claims.index')->withErrors(['claim' => $exception->getMessage()]);
        }

        $approved->load('user');

        return redirect()
            ->route('admin.claims.index')
            ->with('status', "Klaim disetujui. {$approved->user->username} mendapat +{$approved->points_awarded} poin.");
    }
}
