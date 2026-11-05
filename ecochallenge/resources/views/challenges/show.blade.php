@extends('layouts.eco')

@section('title', $challenge->title)

@section('content')
    <a href="{{ route('challenges.index') }}" class="text-sm text-green-800 hover:underline">← Kembali ke katalog</a>

    <article class="mt-4 bg-white rounded-2xl border border-green-100 shadow-sm p-6 sm:p-8">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold bg-green-100 text-green-800 rounded-full px-3 py-1">{{ $challenge->category->name }}</span>
            <span class="text-xs font-bold bg-green-800 text-white rounded-full px-3 py-1">+{{ $challenge->points }} poin</span>
        </div>

        <h1 class="eco-h1 mt-3">{{ $challenge->title }}</h1>
        <p class="text-slate-700 mt-3">{{ $challenge->description }}</p>

        <dl class="grid sm:grid-cols-2 gap-4 mt-6 text-sm">
            <div class="rounded-xl bg-green-50 p-4">
                <dt class="text-slate-500">Tingkat kesulitan</dt>
                <dd class="font-semibold mt-1">{{ $challenge->difficulty }}</dd>
            </div>
            <div class="rounded-xl bg-green-50 p-4">
                <dt class="text-slate-500">Estimasi emisi yang dihindari</dt>
                <dd class="font-semibold mt-1">{{ number_format($challenge->estimated_co2e_gram) }} g CO₂e</dd>
                @if ($challenge->emission_source)
                    <dd class="text-xs text-slate-500 mt-1">Sumber: {{ $challenge->emission_source }}</dd>
                @endif
            </div>
        </dl>

        {{-- PBI-14: kriteria bukti dibaca sebelum unggah --}}
        <section class="mt-6">
            <h2 class="eco-h2">Kriteria bukti foto</h2>
            <p class="text-slate-700 mt-1">{{ $challenge->proof_criteria ?: 'Kriteria bukti untuk challenge ini belum diisi.' }}</p>
            <p class="text-xs text-slate-500 mt-2">Poin adalah apresiasi partisipasi. Estimasi emisi bersifat perkiraan, bukan hasil pengukuran.</p>
        </section>

        {{-- Tombol klaim dikerjakan Alexander (PBI-15). Tampil otomatis bila route-nya sudah ada. --}}
        @if (Route::has('claims.create'))
            <a href="{{ route('claims.create', $challenge) }}" class="eco-btn mt-6 inline-flex rounded-lg bg-green-800 px-5 py-2.5 hover:bg-green-700">
                Kirim bukti
            </a>
        @endif
    </article>
@endsection
