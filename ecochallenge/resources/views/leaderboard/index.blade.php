@extends('layouts.eco')

@section('title', 'Leaderboard')

@section('content')
@php
    $podium = count($rows) >= 3 ? array_slice($rows, 0, 3) : [];
    $rest = count($rows) >= 3 ? array_slice($rows, 3) : $rows;
    $max = max(1, collect($rows)->max('total_points') ?? 1);

    // Gaya podium per peringkat: [urutan visual, tinggi alas, warna lencana, ikon]
    $style = [
        1 => ['order' => 'order-2', 'base' => 'h-24 sm:h-28', 'badge' => 'from-amber-300 to-yellow-500 text-amber-950', 'ring' => 'ring-amber-300'],
        2 => ['order' => 'order-1', 'base' => 'h-16 sm:h-20', 'badge' => 'from-slate-200 to-slate-400 text-slate-800', 'ring' => 'ring-slate-300'],
        3 => ['order' => 'order-3', 'base' => 'h-12 sm:h-16', 'badge' => 'from-orange-300 to-orange-500 text-orange-950', 'ring' => 'ring-orange-300'],
    ];
@endphp

<div class="space-y-8">
    <header class="animate-fade-up">
        <p class="text-[0.7rem] font-bold tracking-[0.2em] text-eco-700">TUMBUH BERSAMA</p>
        <h1 class="mt-1 text-[clamp(1.75rem,4vw,2.5rem)] font-bold leading-[1.1] tracking-tight text-eco-900">Papan aksi baik.</h1>
        <p class="mt-2 max-w-2xl text-slate-600">Peringkat mahasiswa menurut total poin dari klaim yang disetujui. Nama yang tampil adalah username.</p>
    </header>

    {{-- Posisi sendiri --}}
    @if ($myRank !== null)
        <section role="status" class="relative animate-fade-up overflow-hidden rounded-3xl bg-gradient-to-br from-eco-950 via-eco-900 to-eco-700 p-5 text-white shadow-lift [animation-delay:60ms] sm:p-7">
            <div class="pointer-events-none absolute inset-0 opacity-[0.12]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>
            <div class="pointer-events-none absolute -right-12 -top-12 size-56 rounded-full bg-lime-eco/25 blur-3xl"></div>
            <div class="relative flex items-center justify-between gap-5">
                <div>
                    <p class="text-[0.7rem] font-bold tracking-[0.2em] text-lime-eco">POSISI KAMU</p>
                    <p class="mt-1 text-[clamp(2.5rem,8vw,3.75rem)] font-bold leading-none tracking-tighter tabular-nums">#{{ $myRank }}</p>
                    <p class="mt-2 text-sm text-eco-100">
                        @if ($myRank === 1)
                            Kamu memimpin papan peringkat. Pertahankan!
                        @elseif ($gap !== null)
                            <strong class="font-semibold text-white">{{ number_format($gap) }} poin</strong> lagi untuk naik ke #{{ $myRank - 1 }}.
                        @else
                            Kirim lebih banyak bukti untuk naik peringkat.
                        @endif
                    </p>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-2 text-right">
                    <x-ui.avatar :name="auth()->user()->username" :seed="auth()->user()->username" class="size-12 text-base ring-2 ring-white/30" />
                    <div>
                        <p class="text-sm font-semibold">{{ auth()->user()->username }}</p>
                        <p class="text-xs text-eco-100"><span class="text-lg font-bold text-lime-eco tabular-nums">{{ number_format(auth()->user()->total_points) }}</span> poin</p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (count($rows) === 0)
        <section class="animate-fade-up rounded-3xl border border-dashed border-eco-200 bg-white/70 p-10 text-center sm:p-14">
            <span class="mx-auto grid size-16 place-items-center rounded-3xl bg-eco-100 text-eco-700"><x-ui.icon name="trophy" class="size-8" /></span>
            <h2 class="mt-4 text-xl font-bold text-eco-900 tracking-tight">Belum ada peringkat</h2>
            <p class="mx-auto mt-1 max-w-sm text-slate-600">Peringkat muncul setelah ada mahasiswa yang klaimnya disetujui.</p>
            @if (Route::has('challenges.index'))
                <a href="{{ route('challenges.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-eco-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-eco-700">
                    Jadi yang pertama <x-ui.icon name="arrow-right" class="size-4" />
                </a>
            @endif
        </section>
    @else
        {{-- Podium tiga besar --}}
        @if ($podium)
            <section aria-label="Tiga besar" class="animate-fade-up [animation-delay:120ms]">
                <ol class="grid grid-cols-3 items-end gap-2.5 sm:gap-5">
                    @foreach ($podium as $row)
                        @php
                            $s = $style[$row['rank']];
                            $isMe = $row['id'] === $myId;
                        @endphp
                        <li class="{{ $s['order'] }} flex min-w-0 flex-col items-center"
                            data-row data-rank="{{ $row['rank'] }}" data-username="{{ $row['username'] }}"
                            @if ($isMe) data-me="true" aria-current="true" @endif>
                            <div class="relative mb-2 flex flex-col items-center">
                                @if ($row['rank'] === 1)
                                    <x-ui.icon name="crown" class="mb-1 size-6 text-amber-500 drop-shadow sm:size-7" />
                                @endif
                                <x-ui.avatar :name="$row['username']" :seed="$row['username']"
                                             class="{{ $row['rank'] === 1 ? 'size-16 text-xl sm:size-20 sm:text-2xl' : 'size-12 text-base sm:size-16 sm:text-xl' }} ring-4 {{ $s['ring'] }} shadow-lg" />
                                @if ($isMe)
                                    <span class="absolute -bottom-2 rounded-full bg-eco-800 px-2 py-0.5 text-[0.65rem] font-bold text-white shadow">KAMU</span>
                                @endif
                            </div>
                            <p class="w-full truncate text-center text-sm font-semibold text-eco-900 sm:text-base">{{ $row['username'] }}</p>
                            <p class="text-xs font-semibold text-eco-600 tabular-nums sm:text-sm">{{ number_format($row['total_points']) }} poin</p>
                            <div class="{{ $s['base'] }} mt-2.5 grid w-full place-items-start justify-items-center rounded-t-2xl bg-gradient-to-b {{ $s['badge'] }} pt-2 shadow-inner">
                                <span class="text-2xl font-extrabold opacity-90 sm:text-3xl">{{ $row['rank'] }}</span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        {{-- Daftar peringkat --}}
        @if (count($rest) || $myRow)
            <section aria-label="Daftar peringkat" class="animate-fade-up overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-black/5 [animation-delay:180ms]">
                <ol class="divide-y divide-slate-100">
                    @foreach ($rest as $row)
                        @php $isMe = $row['id'] === $myId; @endphp
                        <li class="flex items-center gap-3 px-4 py-3.5 transition sm:gap-4 sm:px-6 {{ $isMe ? 'bg-eco-50 ring-1 ring-inset ring-eco-200' : 'hover:bg-slate-50' }}"
                            data-row data-rank="{{ $row['rank'] }}" data-username="{{ $row['username'] }}"
                            @if ($isMe) data-me="true" aria-current="true" @endif>
                            <span class="w-8 shrink-0 text-center text-sm font-bold tabular-nums {{ $isMe ? 'text-eco-800' : 'text-slate-400' }}">{{ $row['rank'] }}</span>
                            <x-ui.avatar :name="$row['username']" :seed="$row['username']" class="size-10" />
                            <div class="min-w-0 flex-1">
                                <p class="flex items-center gap-2 truncate text-sm font-semibold text-eco-900 sm:text-base">
                                    {{ $row['username'] }}
                                    @if ($isMe)<span class="rounded-full bg-eco-800 px-2 py-0.5 text-[0.65rem] font-bold text-white">KAMU</span>@endif
                                </p>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
                                    <div class="h-full rounded-full {{ $isMe ? 'bg-eco-700' : 'bg-eco-300' }}" style="width:{{ max(2, round($row['total_points'] / $max * 100)) }}%"></div>
                                </div>
                            </div>
                            <p class="shrink-0 text-right text-sm font-bold text-eco-800 tabular-nums sm:text-base">{{ number_format($row['total_points']) }}<span class="ml-1 hidden text-xs font-medium text-slate-400 sm:inline">poin</span></p>
                        </li>
                    @endforeach

                    @if ($myRow)
                        <li class="flex justify-center py-1 text-slate-300" aria-hidden="true">⋮</li>
                        <li class="flex items-center gap-3 bg-eco-50 px-4 py-3.5 ring-1 ring-inset ring-eco-200 sm:gap-4 sm:px-6"
                            data-row data-rank="{{ $myRow['rank'] }}" data-username="{{ $myRow['username'] }}" data-me="true" aria-current="true">
                            <span class="w-8 shrink-0 text-center text-sm font-bold tabular-nums text-eco-800">{{ $myRow['rank'] }}</span>
                            <x-ui.avatar :name="$myRow['username']" :seed="$myRow['username']" class="size-10" />
                            <p class="flex min-w-0 flex-1 items-center gap-2 truncate text-sm font-semibold text-eco-900 sm:text-base">
                                {{ $myRow['username'] }}
                                <span class="rounded-full bg-eco-800 px-2 py-0.5 text-[0.65rem] font-bold text-white">KAMU</span>
                            </p>
                            <p class="shrink-0 text-right text-sm font-bold text-eco-800 tabular-nums sm:text-base">{{ number_format($myRow['total_points']) }}<span class="ml-1 hidden text-xs font-medium text-slate-400 sm:inline">poin</span></p>
                        </li>
                    @endif
                </ol>
            </section>
        @endif

        <p class="flex items-start gap-2 text-xs text-slate-500">
            <x-ui.icon name="circle-alert" class="mt-0.5 size-3.5 shrink-0" />
            Menampilkan {{ $limit }} besar. Bila poin sama, urutan mengikuti username. Poin adalah ukuran partisipasi, bukan jumlah emisi yang dikurangi.
        </p>
    @endif
</div>
@endsection
