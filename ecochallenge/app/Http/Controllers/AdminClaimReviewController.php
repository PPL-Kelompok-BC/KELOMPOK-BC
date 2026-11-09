<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClaimReviewRequest;
use App\Models\Claim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminClaimReviewController extends Controller
{
    /** PBI-36: daftar klaim, pending di atas. */
    public function index(): View
    {
        $claims = Claim::with(['challenge', 'user'])
            ->orderByRaw("CASE WHEN status = '".Claim::STATUS_PENDING."' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        return view('admin.claims.index', compact('claims'));
    }

    /** PBI-37: detail klaim (foto + kriteria bukti). */
    public function show(Claim $claim): View
    {
        $claim->load(['challenge', 'user']);

        return view('admin.claims.show', compact('claim'));
    }

    /** PBI-38: tandai sesuai (approve) atau batalkan (reject), dengan koreksi award. */
    public function review(ClaimReviewRequest $request, Claim $claim): RedirectResponse
    {
        if ($claim->status !== Claim::STATUS_PENDING) {
            return back()->with('error', 'Klaim ini sudah direview.');
        }

        $decision = $request->validated('decision');

        DB::transaction(function () use ($claim, $decision, $request) {
            $approve = $decision === 'approve';
            $challenge = $claim->challenge;

            $claim->update([
                'status' => $approve ? Claim::STATUS_APPROVED : Claim::STATUS_REJECTED,
                'points_awarded' => $approve ? $challenge->points : 0,
                'co2e_awarded_gram' => $approve ? $challenge->estimated_co2e_gram : 0,
                'cancel_reason' => $approve ? null : $request->validated('cancel_reason'),
                'needs_review' => false,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            if ($approve) {
                $claim->user->increment('total_points', $challenge->points);
            }
        });

        return redirect()
            ->route('admin.claims.index')
            ->with('success', $decision === 'approve'
                ? 'Klaim disetujui dan poin diberikan.'
                : 'Klaim dibatalkan.');
    }
}
