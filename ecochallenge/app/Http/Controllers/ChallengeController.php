<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChallengeController extends Controller
{
    /** PBI-12 dan PBI-13: daftar challenge aktif dengan filter kategori. */
    public function index(Request $request): View
    {
        $selectedCategory = $request->integer('kategori') ?: null;

        $categories = Category::orderBy('name')->get();

        $challenges = Challenge::active()
            ->with('category')
            ->when($selectedCategory, fn ($query) => $query->where('category_id', $selectedCategory))
            ->orderBy('title')
            ->get();

        return view('challenges.index', compact('challenges', 'categories', 'selectedCategory'));
    }

    /** PBI-14: detail challenge. Challenge nonaktif menampilkan 404. */
    public function show(Challenge $challenge): View
    {
        abort_unless($challenge->is_active, 404);

        $challenge->load('category');

        return view('challenges.show', compact('challenge'));
    }
}
