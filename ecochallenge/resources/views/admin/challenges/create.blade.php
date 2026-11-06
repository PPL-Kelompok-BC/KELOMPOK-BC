
@extends('layouts.eco')

@section('title', 'Tambah Challenge')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-semibold tracking-widest text-green-700">
            ADMINISTRATOR
        </p>

        <h1 class="eco-h1 mt-1">Tambah Challenge</h1>

        <p class="text-slate-600 mt-1">
            Tambahkan challenge baru untuk mahasiswa.
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6">

        <form action="{{ route('admin.challenges.store') }}" method="POST">
            @csrf

            {{-- Judul Challenge --}}
            <div class="mb-5">
                <label for="title" class="block font-semibold text-sm mb-2">
                    Judul Challenge
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    placeholder="Contoh: Gunakan Transportasi Umum"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    required
                >

                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="mb-5">
                <label for="description" class="block font-semibold text-sm mb-2">
                    Deskripsi
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Jelaskan aktivitas challenge..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    required
                >{{ old('description') }}</textarea>

                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategori --}}
            <div class="mb-5">
                <label for="category_id" class="block font-semibold text-sm mb-2">
                    Kategori
                </label>

                <select
                    id="category_id"
                    name="category_id"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    required
                >
                    <option value="">Pilih kategori</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(old('category_id') == $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                @error('category_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tingkat Kesulitan --}}
            <div class="mb-5">
                <label for="difficulty" class="block font-semibold text-sm mb-2">
                    Tingkat Kesulitan
                </label>

                <select
                    id="difficulty"
                    name="difficulty"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    required
                >
                    <option value="">Pilih tingkat kesulitan</option>
                    <option value="mudah" @selected(old('difficulty') === 'mudah')>
                        Mudah
                    </option>
                    <option value="sedang" @selected(old('difficulty') === 'sedang')>
                        Sedang
                    </option>
                </select>

                @error('difficulty')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Poin --}}
            <div class="mb-5">
                <label for="points" class="block font-semibold text-sm mb-2">
                    Poin
                </label>

                <input
                    type="number"
                    id="points"
                    name="points"
                    value="{{ old('points') }}"
                    min="1"
                    placeholder="Contoh: 20"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    required
                >

                @error('points')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kriteria Bukti --}}
            <div class="mb-5">
                <label for="proof_criteria" class="block font-semibold text-sm mb-2">
                    Kriteria Bukti
                </label>

                <textarea
                    id="proof_criteria"
                    name="proof_criteria"
                    rows="3"
                    placeholder="Contoh: Foto saat menggunakan transportasi umum"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    required
                >{{ old('proof_criteria') }}</textarea>

                @error('proof_criteria')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Estimasi Emisi --}}
            <div class="mb-5">
                <label for="estimated_co2e_gram" class="block font-semibold text-sm mb-2">
                    Estimasi Emisi (gram CO2e)
                </label>

                <input
                    type="number"
                    id="estimated_co2e_gram"
                    name="estimated_co2e_gram"
                    value="{{ old('estimated_co2e_gram') }}"
                    min="0"
                    placeholder="Contoh: 500"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    required
                >

                @error('estimated_co2e_gram')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sumber Emisi --}}
            <div class="mb-5">
                <label for="emission_source" class="block font-semibold text-sm mb-2">
                    Sumber Emisi
                </label>

                <input
                    type="text"
                    id="emission_source"
                    name="emission_source"
                    value="{{ old('emission_source') }}"
                    placeholder="Masukkan sumber perhitungan emisi"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    required
                >

                @error('emission_source')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div class="mb-6">
                <label for="is_active" class="block font-semibold text-sm mb-2">
                    Status Challenge
                </label>

                <select
                    id="is_active"
                    name="is_active"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                    required
                >
                    <option value="1" @selected(old('is_active', '1') == '1')>
                        Aktif
                    </option>
                    <option value="0" @selected(old('is_active') == '0')>
                        Nonaktif
                    </option>
                </select>

                @error('is_active')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="flex flex-wrap gap-3">
                <button
                    type="submit"
                    class="bg-green-800 hover:bg-green-900 text-white rounded-xl px-6 py-3 font-semibold"
                >
                    Simpan Challenge
                </button>

                <a
                    href="{{ route('admin.challenges.index') }}"
                    class="border border-slate-300 hover:bg-slate-100 rounded-xl px-6 py-3 font-semibold"
                >
                    Batal
                </a>
            </div>

        </form>
    </div>
@endsection
