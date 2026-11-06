<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Challenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminChallengeController extends Controller
{
    /**
     * PBI-08: Menampilkan daftar challenge untuk admin.
     */
    public function index(Request $request): View
    {
        // Memastikan hanya admin yang dapat mengakses halaman.
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

    /**
     * PBI-09: Menampilkan formulir tambah challenge.
     */
    public function create(): View
    {
        abort_unless(
            auth()->user()?->role === 'admin',
            403
        );

        // Mengambil kategori yang masih aktif.
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.challenges.create', compact('categories'));
    }

    /**
     * PBI-09: Menyimpan challenge baru.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(
            auth()->user()?->role === 'admin',
            403
        );

        // Validasi seluruh input formulir.
        $validated = $request->validate([
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id,is_active,1',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
            ],
            'difficulty' => [
                'required',
                'in:mudah,sedang',
            ],
            'points' => [
                'required',
                'integer',
                'min:1',
            ],
            'proof_criteria' => [
                'required',
                'string',
            ],
            'estimated_co2e_gram' => [
                'required',
                'integer',
                'min:0',
            ],
            'emission_source' => [
                'required',
                'string',
                'max:255',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan atau tidak aktif.',
            'title.required' => 'Judul challenge wajib diisi.',
            'description.required' => 'Deskripsi challenge wajib diisi.',
            'difficulty.required' => 'Tingkat kesulitan wajib dipilih.',
            'difficulty.in' => 'Tingkat kesulitan harus mudah atau sedang.',
            'points.required' => 'Poin wajib diisi.',
            'points.min' => 'Poin harus lebih besar dari 0.',
            'proof_criteria.required' => 'Kriteria bukti wajib diisi.',
            'estimated_co2e_gram.required' => 'Estimasi emisi wajib diisi.',
            'estimated_co2e_gram.integer' => 'Estimasi emisi harus berupa angka bulat.',
            'estimated_co2e_gram.min' => 'Estimasi emisi tidak boleh negatif.',
            'emission_source.required' => 'Sumber emisi wajib diisi.',
        ]);

        // Menyimpan challenge baru ke database.
        Challenge::create([
            ...$validated,
            'created_by' => auth()->id(),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()
            ->route('admin.challenges.index')
            ->with('success', 'Challenge berhasil ditambahkan.');
    }

    /**
     * PBI-10: Menampilkan formulir edit challenge.
     */
    public function edit(Challenge $challenge): View
    {
        abort_unless(
            auth()->user()?->role === 'admin',
            403
        );

        // Mengambil kategori aktif dan kategori challenge saat ini.
        $categories = Category::where('is_active', true)
            ->orWhere('id', $challenge->category_id)
            ->orderBy('name')
            ->get();

        return view('admin.challenges.edit', compact(
            'challenge',
            'categories'
        ));
    }

    /**
     * PBI-10: Memvalidasi dan menyimpan perubahan challenge.
     */
    public function update(Request $request, Challenge $challenge): RedirectResponse
    {
        abort_unless(
            auth()->user()?->role === 'admin',
            403
        );

        // Validasi data yang dikirim dari formulir edit.
        $validated = $request->validate([
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
            ],
            'difficulty' => [
                'required',
                'in:mudah,sedang',
            ],
            'points' => [
                'required',
                'integer',
                'min:1',
            ],
            'proof_criteria' => [
                'required',
                'string',
            ],
            'estimated_co2e_gram' => [
                'required',
                'integer',
                'min:0',
            ],
            'emission_source' => [
                'required',
                'string',
                'max:255',
            ],
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'title.required' => 'Judul challenge wajib diisi.',
            'description.required' => 'Deskripsi challenge wajib diisi.',
            'difficulty.required' => 'Tingkat kesulitan wajib dipilih.',
            'difficulty.in' => 'Tingkat kesulitan harus mudah atau sedang.',
            'points.required' => 'Poin wajib diisi.',
            'points.integer' => 'Poin harus berupa angka bulat.',
            'points.min' => 'Poin harus lebih besar dari 0.',
            'proof_criteria.required' => 'Kriteria bukti wajib diisi.',
            'estimated_co2e_gram.required' => 'Estimasi emisi wajib diisi.',
            'estimated_co2e_gram.integer' => 'Estimasi emisi harus berupa angka bulat.',
            'estimated_co2e_gram.min' => 'Estimasi emisi tidak boleh negatif.',
            'emission_source.required' => 'Sumber emisi wajib diisi.',
        ]);

        // Hanya memperbarui data challenge.
        // Poin klaim yang sudah tersimpan tidak ikut berubah.
        $challenge->update($validated);

        return redirect()
            ->route('admin.challenges.index')
            ->with('success', 'Challenge berhasil diperbarui.');
    }
}
