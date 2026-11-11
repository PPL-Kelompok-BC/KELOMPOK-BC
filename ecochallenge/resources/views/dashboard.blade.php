@extends('layouts.eco')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">

    {{-- Sambutan --}}
    <section class="relative animate-fade-up overflow-hidden rounded-3xl bg-gradient-to-br from-eco-950 via-eco-900 to-eco-700 p-6 text-white shadow-lift sm:p-9">
        <div class="pointer-events-none absolute inset-0 opacity-[0.12]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>
        <div class="pointer-events-none absolute -right-16 -top-16 size-72 rounded-full bg-lime-eco/25 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4 sm:gap-5">
                <x-ui.avatar :name="$user->name" :seed="$user->username"
                             :src="$user->profile_photo_path ? route('profile.photo', $user).'?v='.md5($user->profile_photo_path) : null"
                             class="size-16 text-xl ring-4 ring-white/20 sm:size-20 sm:text-2xl" />
                <div class="min-w-0">
                    <p class="text-[0.7rem] font-bold tracking-[0.2em] text-lime-eco">DASHBOARD MAHASISWA</p>
                    <h1 class="mt-1 text-2xl font-bold leading-tight tracking-tight sm:text-3xl">{{ $greeting }}, {{ $user->name }}.</h1>
                    <p class="mt-1 text-sm text-eco-100">Kamu masuk sebagai <strong class="font-semibold text-white">{{ $user->username }}</strong>. Satu aksi kecil hari ini sudah cukup.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('challenges.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-lime-eco px-5 py-3 text-sm font-semibold text-eco-900 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:brightness-95">
                    <x-ui.icon name="target" class="size-4" /> Pilih challenge
                </a>
                <a href="{{ route('leaderboard.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-5 py-3 text-sm font-semibold backdrop-blur transition hover:bg-white/20">
                    <x-ui.icon name="trophy" class="size-4" /> Leaderboard
                </a>
            </div>
        </div>
    </section>

    {{-- Angka ringkas --}}
    <section aria-label="Ringkasan" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-ui.stat icon="award" label="Total poin" :value="number_format($points)" hint="Dari klaim yang disetujui" class="animate-fade-up [animation-delay:60ms]" />
        <x-ui.stat icon="trophy" tone="amber" label="Peringkat" :value="$rank ? '#'.$rank : '–'"
                   :hint="$participants ? 'dari '.$participants.' mahasiswa' : 'Belum ada peserta'" class="animate-fade-up [animation-delay:120ms]" />
        <x-ui.stat icon="hourglass" tone="sky" label="Menunggu" :value="$pending" hint="Bukti menunggu ditinjau" class="animate-fade-up [animation-delay:180ms]" />
        <x-ui.stat icon="circle-check" tone="violet" label="Disetujui" :value="$approved" hint="Aksi terverifikasi" class="animate-fade-up [animation-delay:240ms]" />
    </section>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">

        {{-- Challenge pilihan (min-w-0: item grid tidak boleh melebar melewati layar HP) --}}
        <section aria-labelledby="judul-pilihan" class="min-w-0">
            <div class="mb-4 flex items-end justify-between gap-4">
                <div>
                    <p class="text-[0.7rem] font-bold tracking-[0.2em] text-eco-700">MULAI DARI SINI</p>
                    <h2 id="judul-pilihan" class="mt-1 text-xl font-bold text-eco-900 tracking-tight">Challenge pilihan hari ini</h2>
                </div>
                <a href="{{ route('challenges.index') }}" class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-eco-700 hover:text-eco-900">
                    Semua <x-ui.icon name="arrow-right" class="size-4" />
                </a>
            </div>

            @forelse ($featured as $challenge)
                <a href="{{ route('challenges.show', $challenge) }}"
                   class="group mb-3 flex items-center gap-4 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-lift focus-visible:ring-2 focus-visible:ring-eco-500">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-eco-100 text-eco-700 transition group-hover:bg-eco-800 group-hover:text-lime-eco">
                        <x-ui.icon name="leaf" class="size-6" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-xs font-semibold text-eco-600">{{ $challenge->category->name }}</span>
                        <span class="block truncate text-base font-semibold text-eco-900">{{ $challenge->title }}</span>
                        <span class="block text-xs text-slate-500">{{ $challenge->difficulty }} · ±{{ number_format($challenge->estimated_co2e_gram) }} g CO₂e</span>
                    </span>
                    <span class="shrink-0 rounded-full bg-lime-eco px-3 py-1 text-sm font-bold text-eco-900">+{{ $challenge->points }}</span>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-eco-200 bg-white/60 p-8 text-center">
                    <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-eco-100 text-eco-700"><x-ui.icon name="sparkles" class="size-6" /></span>
                    <p class="mt-3 font-semibold text-eco-900">Belum ada challenge aktif</p>
                    <p class="mt-1 text-sm text-slate-500">Challenge baru akan muncul di sini setelah ditambahkan admin.</p>
                </div>
            @endforelse
        </section>

        {{-- Aktivitas terbaru --}}
        <section aria-labelledby="judul-aktivitas" class="min-w-0">
            <div class="mb-4 flex items-end justify-between gap-4">
                <div>
                    <p class="text-[0.7rem] font-bold tracking-[0.2em] text-eco-700">JEJAK LANGKAHMU</p>
                    <h2 id="judul-aktivitas" class="mt-1 text-xl font-bold text-eco-900 tracking-tight">Aktivitas terbaru</h2>
                </div>
                <a href="{{ route('claims.index') }}" class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-eco-700 hover:text-eco-900">
                    Riwayat <x-ui.icon name="arrow-right" class="size-4" />
                </a>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-black/5">
                @forelse ($recentClaims as $claim)
                    <a href="{{ route('claims.show', $claim) }}"
                       class="flex items-center gap-3 border-b border-slate-100 p-4 transition last:border-0 hover:bg-eco-50">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-eco-50 text-eco-700"><x-ui.icon name="camera" class="size-[1.1rem]" /></span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-eco-900">{{ $claim->challenge->title }}</span>
                            <span class="block text-xs text-slate-500">{{ $claim->claim_date->translatedFormat('j M Y') }}@if ($claim->status === 'approved') · +{{ $claim->points_awarded }} poin @endif</span>
                        </span>
                        <x-claim-status :status="$claim->status" />
                    </a>
                @empty
                    <div class="p-8 text-center">
                        <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-eco-50 text-eco-600"><x-ui.icon name="camera" class="size-6" /></span>
                        <p class="mt-3 font-semibold text-eco-900">Belum ada aksi</p>
                        <p class="mx-auto mt-1 max-w-xs text-sm text-slate-500">Pilih satu challenge, kirim foto buktinya, dan aksimu akan tercatat di sini.</p>
                        <a href="{{ route('challenges.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-eco-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-eco-700">
                            Mulai challenge pertama <x-ui.icon name="arrow-right" class="size-4" />
                        </a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
