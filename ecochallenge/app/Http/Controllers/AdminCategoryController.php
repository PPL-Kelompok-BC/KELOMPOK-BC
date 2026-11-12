<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Admin.');
        }

        $categories = Category::withCount('challenges')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }
}