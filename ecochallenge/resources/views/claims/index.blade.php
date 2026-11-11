@extends('layouts.eco')

@section('title', 'Riwayat Klaim')

@section('content')
    <h1 class="eco-h1">Klaim saya</h1>
    <p class="text-slate-600 mt-1">Riwayat bukti challenge yang pernah kamu kirim.</p>

    @if ($claims->isEmpty())
        <div class="mt-6 bg-white rounded-2xl border border-green-100 shadow-sm p-8 text-center">
            <p class="text-slate-500">Belum ada klaim. Mulai dari <a href="{{ route('challenges.index') }}" class="text-green-800 hover:underline">katalog challenge</a>.</p>
        </div>
    @else
        <div class="mt-6 space-y-4">
            @foreach ($claims as $claim)
                <a href="{{ route('claims.show', $claim) }}"
                   class="block bg-white rounded-2xl border border-green-100 shadow-sm p-5 hover:border-green-300">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $claim->challenge->title }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $claim->claim_date->format('d M Y') }}</p>
                        </div>
                        <x-claim-status :status="$claim->status" />
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection