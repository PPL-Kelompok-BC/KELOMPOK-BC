@extends('layouts.eco')

@section('title', 'Katalog Challenge')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-semibold tracking-widest text-green-700">AKSI HARI INI</p>
        <h1 class="eco-h1 mt-1">Temukan challenge-mu.</h1>
        <p class="text-slate-600 mt-1">Pilih aksi kecil yang cocok dengan kegiatanmu hari ini.</p>
    </div>

    {{-- PBI-13: filter kategori --}}
    <div class="flex flex-wrap gap-2 mb-6" aria-label="Filter kategori">
        <a href="{{ route('challenges.index') }}"
           class="px-4 py-2 rounded-full text-sm border {{ $selectedCategory === null ? 'eco-btn bg-green-800 text-white border-green-800' : 'bg-white border-green-200 hover:bg-green-100' }}">
            Semua
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('challenges.index', ['kategori' => $category->id]) }}"
               class="px-4 py-2 rounded-full text-sm border {{ $selectedCategory === $category->id ? 'eco-btn bg-green-800 text-white border-green-800' : 'bg-white border-green-200 hover:bg-green-100' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    <p class="text-sm text-slate-600 mb-4" aria-live="polite">{{ $challenges->count() }} challenge tersedia</p>

    {{-- PBI-12: daftar challenge aktif --}}
    @if ($challenges->isEmpty())
        <div class="bg-white rounded-2xl border border-green-100 p-10 text-center">
            <h2 class="eco-h2">
                {{ $selectedCategory ? 'Belum ada challenge di kategori ini' : 'Belum ada challenge' }}
            </h2>
            <p class="text-slate-600 mt-1">Coba pilih kategori lain.</p>
            @if ($selectedCategory)
                <a href="{{ route('challenges.index') }}" class="eco-btn inline-block mt-4 px-4 py-2 rounded-lg bg-green-800 text-sm">Lihat semua challenge</a>
            @endif
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($challenges as $challenge)
                <article class="bg-white rounded-2xl border border-green-100 shadow-sm flex flex-col overflow-hidden">
                    <div class="bg-green-100 px-5 py-4 flex items-center justify-between">
                        <span class="text-xs font-semibold text-green-800">{{ $challenge->category->name }}</span>
                        <span class="text-xs font-bold bg-green-800 text-white rounded-full px-3 py-1">+{{ $challenge->points }} poin</span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <h2 class="eco-h2">{{ $challenge->title }}</h2>
                        <p class="text-sm text-slate-600 mt-1 line-clamp-3">{{ $challenge->description }}</p>
                        <dl class="mt-4 text-sm space-y-1">
                            <div class="flex justify-between"><dt class="text-slate-500">Kesulitan</dt><dd class="font-medium">{{ $challenge->difficulty }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Estimasi emisi</dt><dd class="font-medium">{{ number_format($challenge->estimated_co2e_gram) }} g CO₂e</dd></div>
                        </dl>
                        <a href="{{ route('challenges.show', $challenge) }}"
                           class="eco-btn mt-5 inline-flex justify-center rounded-lg bg-green-800 text-sm py-2 hover:bg-green-700">
                            Lihat detail
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
