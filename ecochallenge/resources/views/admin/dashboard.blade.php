@extends('layouts.eco')

@section('title', 'Dashboard Admin')

@section('content')
@php
    $user = auth()->user();
    $tiles = collect([
        ['label' => 'Kelola challenge', 'desc' => 'Lihat, filter, dan atur status challenge.', 'icon' => 'list-checks', 'route' => 'admin.challenges.index'],
        ['label' => 'Tambah challenge', 'desc' => 'Buat challenge baru lengkap dengan kriteria bukti.', 'icon' => 'plus', 'route' => 'admin.challenges.create'],
        ['label' => 'Tinjau klaim', 'desc' => 'Setujui atau batalkan bukti dari mahasiswa.', 'icon' => 'badge-check', 'route' => 'admin.claims.index'],
        ['label' => 'Leaderboard', 'desc' => 'Pantau peringkat mahasiswa menurut poin.', 'icon' => 'trophy', 'route' => 'leaderboard.index'],
    ])->filter(fn ($t) => Route::has($t['route']));
@endphp

<div class="space-y-8">
    <section class="relative animate-fade-up overflow-hidden rounded-3xl bg-gradient-to-br from-eco-950 via-eco-900 to-eco-700 p-6 text-white shadow-lift sm:p-9">
        <div class="pointer-events-none absolute inset-0 opacity-[0.12]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>
        <div class="pointer-events-none absolute -right-16 -top-16 size-72 rounded-full bg-lime-eco/25 blur-3xl"></div>
        <div class="relative flex items-center gap-4 sm:gap-5">
            <span class="grid size-16 shrink-0 place-items-center rounded-2xl bg-white/10 text-lime-eco ring-4 ring-white/10 sm:size-20">
                <x-ui.icon name="shield-check" class="size-8 sm:size-10" />
            </span>
            <div class="min-w-0">
                <p class="text-[0.7rem] font-bold tracking-[0.2em] text-lime-eco">DASHBOARD ADMIN</p>
                <h1 class="mt-1 text-2xl font-bold leading-tight tracking-tight sm:text-3xl">Halo, {{ $user->name }}.</h1>
                <p class="mt-1 text-sm text-eco-100">Kamu masuk sebagai <strong class="font-semibold text-white">admin</strong>. Berikut ringkasan EcoChallenge hari ini.</p>
            </div>
        </div>
    </section>

    <section aria-label="Ringkasan" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-ui.stat icon="users" label="Mahasiswa aktif" :value="number_format($students)" hint="Ikut peringkat" class="animate-fade-up [animation-delay:60ms]" />
        <x-ui.stat icon="target" tone="violet" label="Challenge aktif" :value="number_format($challenges)" hint="Tampil di katalog" class="animate-fade-up [animation-delay:120ms]" />
        <x-ui.stat icon="hourglass" tone="amber" label="Klaim menunggu" :value="number_format($pending)" hint="Perlu ditinjau" class="animate-fade-up [animation-delay:180ms]" />
        <x-ui.stat icon="circle-check" tone="sky" label="Klaim disetujui" :value="number_format($approved)" hint="Poin sudah diberikan" class="animate-fade-up [animation-delay:240ms]" />
    </section>

    <section aria-labelledby="judul-pintasan">
        <p class="text-[0.7rem] font-bold tracking-[0.2em] text-eco-700">PINTASAN</p>
        <h2 id="judul-pintasan" class="mb-4 mt-1 text-xl font-bold text-eco-900 tracking-tight">Yang sering dikerjakan</h2>
        <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
            @foreach ($tiles as $tile)
                <a href="{{ route($tile['route']) }}"
                   class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-lift focus-visible:ring-2 focus-visible:ring-eco-500 sm:p-5">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-eco-100 text-eco-700 transition group-hover:bg-eco-800 group-hover:text-lime-eco"><x-ui.icon :name="$tile['icon']" class="size-6" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-eco-900">{{ $tile['label'] }}</span>
                        <span class="block text-sm text-slate-500">{{ $tile['desc'] }}</span>
                    </span>
                    <x-ui.icon name="arrow-up-right" class="size-5 text-slate-300 transition group-hover:text-eco-700" />
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection
