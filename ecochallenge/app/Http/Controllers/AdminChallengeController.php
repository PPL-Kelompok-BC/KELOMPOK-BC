<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminChallengeController extends Controller
{
    /**
     * PBI-08: Menampilkan daftar challenge untuk admin.
     */
    public function index(Request $request): View
    {
        // Hanya admin yang boleh mengakses halaman ini.
        abort_unless(
            auth()->user()?->role === 'admin',
            403
        );

        // Mengambil status filter dari URL.
        $status = $request->query('status', 'all');

        // Memastikan pilihan filter valid.
        if (! in_array($status, ['all', 'active', 'inactive'], true)) {
            $status = 'all';
        }

        // Mengambil challenge beserta kategori.
        $challenges = Challenge::with('category')
            ->when($status === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($status === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->orderBy('title')
            ->get();

        return view('admin.challenges.index', compact(
            'challenges',
            'status'
        ));
    }
}