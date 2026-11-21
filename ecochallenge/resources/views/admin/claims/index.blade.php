@extends('layouts.eco')

@section('title', 'Tinjau Klaim')

@section('content')
{{-- PBI-19 (SCRUM-31): admin menerima klaim, poin mahasiswa bertambah otomatis. --}}
<div class="space-y-6">
    <header>
        <p class="text-[0.7rem] font-bold tracking-[0.2em] text-eco-700">ADMIN</p>
        <h1 class="mt-1 text-[clamp(1.75rem,4vw,2.5rem)] font-bold leading-[1.1] tracking-tight text-eco-900">Tinjau klaim.</h1>
        <p class="mt-2 max-w-2xl text-slate-600">Klaim yang menunggu tinjauan. Saat klaim disetujui, poin challenge langsung ditambahkan ke total poin mahasiswa dan leaderboard ikut diperbarui.</p>
    </header>

    @if (session('status'))
        <div role="status" dusk="claim-review-status" class="flex items-start gap-3 rounded-2xl border border-eco-200 bg-eco-50 p-4 text-eco-900">
            <x-ui.icon name="circle-check" class="mt-0.5 size-5 text-eco-700" />
            <p class="text-sm font-medium">{{ session('status') }}</p>
        </div>
    @endif

    @error('claim')
        <div role="alert" dusk="claim-review-error" class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
            <x-ui.icon name="circle-alert" class="mt-0.5 size-5" />
            <p class="text-sm font-medium">{{ $message }}</p>
        </div>
    @enderror

    @if ($claims->isEmpty())
        <section class="rounded-3xl border border-dashed border-eco-200 bg-white/70 p-10 text-center sm:p-14">
            <span class="mx-auto grid size-16 place-items-center rounded-3xl bg-eco-100 text-eco-700"><x-ui.icon name="badge-check" class="size-8" /></span>
            <h2 class="mt-4 text-xl font-bold tracking-tight text-eco-900">Tidak ada klaim menunggu</h2>
            <p class="mx-auto mt-1 max-w-sm text-slate-600">Semua klaim sudah ditinjau.</p>
        </section>
    @else
        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($claims as $claim)
                <li dusk="claim-{{ $claim->id }}" class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-black/5">
                    @if ($claim->proof_path)
                        <a href="{{ route('claims.photo', $claim) }}" target="_blank" rel="noopener" class="block aspect-video bg-eco-50">
                            <img src="{{ route('claims.photo', $claim) }}" alt="Bukti klaim {{ $claim->challenge->title }}" class="size-full object-cover" loading="lazy">
                        </a>
                    @endif
                    <div class="flex flex-1 flex-col gap-3 p-4 sm:p-5">
                        <div>
                            <p class="font-semibold text-eco-900">{{ $claim->challenge->title }}</p>
                            <p class="text-sm text-slate-500">
                                oleh <strong class="font-semibold text-slate-700">{{ $claim->user->username }}</strong>
                                · {{ $claim->claim_date?->format('d M Y') }}
                                · total sekarang {{ number_format($claim->user->total_points) }} poin
                            </p>
                        </div>
                        @if ($claim->note)
                            <p class="rounded-xl bg-slate-50 p-3 text-sm text-slate-700">{{ $claim->note }}</p>
                        @endif
                        <form method="POST" action="{{ route('admin.claims.approve', $claim) }}" class="mt-auto">
                            @csrf
                            <button type="submit" dusk="approve-{{ $claim->id }}"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-eco-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-eco-700 focus-visible:ring-2 focus-visible:ring-eco-500">
                                <x-ui.icon name="check" class="size-4" />
                                Setujui (+{{ $claim->challenge->points }} poin)
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
