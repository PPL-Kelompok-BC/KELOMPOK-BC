@extends('layouts.eco')

@section('title', 'Detail Klaim')

@section('content')
    <a href="{{ route('admin.claims.index') }}" class="text-sm text-green-800 hover:underline">← Kembali ke daftar klaim</a>

    <article class="mt-4 bg-white rounded-2xl border border-green-100 shadow-sm p-6 sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h1 class="eco-h1">{{ $claim->challenge->title }}</h1>
            <x-claim-status :status="$claim->status" />
        </div>

        <dl class="grid sm:grid-cols-2 gap-4 mt-6 text-sm">
            <div class="rounded-xl bg-green-50 p-4">
                <dt class="text-slate-500">Mahasiswa</dt>
                <dd class="font-semibold mt-1">{{ $claim->user->name }}</dd>
            </div>
            <div class="rounded-xl bg-green-50 p-4">
                <dt class="text-slate-500">Tanggal klaim</dt>
                <dd class="font-semibold mt-1">{{ $claim->claim_date->format('d M Y') }}</dd>
            </div>
        </dl>

        @if ($claim->status === \App\Models\Claim::STATUS_REJECTED && $claim->cancel_reason)
            <div class="mt-6 rounded-xl bg-red-50 border border-red-200 p-4">
                <p class="font-semibold text-red-700">Alasan pembatalan</p>
                <p class="text-red-700 mt-1">{{ $claim->cancel_reason }}</p>
            </div>
        @endif

        @if ($claim->note)
            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                <p class="font-semibold text-slate-600">Keterangan mahasiswa</p>
                <p class="text-slate-700 mt-1">{{ $claim->note }}</p>
            </div>
        @endif

        <section class="mt-6">
            <h2 class="eco-h2">Foto bukti</h2>
            <img src="{{ route('claims.photo', $claim) }}"
                 alt="Foto bukti {{ $claim->challenge->title }}"
                 class="mt-3 max-w-full rounded-xl border border-green-100"
                 loading="lazy">
        </section>

        <section class="mt-6 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm">
            <p class="font-semibold text-amber-800">Kriteria bukti</p>
            <p class="text-amber-800 mt-1">{{ $claim->challenge->proof_criteria }}</p>
        </section>
    </article>

    @if ($claim->status === \App\Models\Claim::STATUS_PENDING)
        <article class="mt-6 bg-white rounded-2xl border border-green-100 shadow-sm p-6 sm:p-8">
            <h2 class="eco-h2">Tinjau klaim</h2>

            @if ($errors->any())
                <div class="mt-4 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-4 grid sm:grid-cols-2 gap-4">
                <form method="POST" action="{{ route('admin.claims.review', $claim) }}">
                    @csrf
                    <input type="hidden" name="decision" value="approve">
                    <button type="submit"
                            class="w-full rounded-xl bg-green-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-900">
                        Tandai sesuai & beri poin
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.claims.review', $claim) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="decision" value="reject">

                    <div>
                        <label for="cancel_reason" class="block text-sm font-semibold text-slate-700">Alasan pembatalan</label>
                        <textarea id="cancel_reason" name="cancel_reason" rows="3" maxlength="255"
                                  class="mt-2 block w-full rounded-xl border border-red-200 p-3 text-sm focus:border-red-500 focus:ring-red-500"
                                  placeholder="Jelaskan mengapa bukti tidak sesuai...">{{ old('cancel_reason') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                        Batalkan klaim
                    </button>
                </form>
            </div>
        </article>
    @endif
@endsection