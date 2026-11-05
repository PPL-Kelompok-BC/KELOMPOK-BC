@extends('layouts.eco')

@section('title', 'Detail Klaim')

@section('content')
    <a href="{{ route('claims.index') }}" class="text-sm text-green-800 hover:underline">← Kembali ke riwayat</a>

    <article class="mt-4 bg-white rounded-2xl border border-green-100 shadow-sm p-6 sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h1 class="eco-h1">{{ $claim->challenge->title }}</h1>
            <x-claim-status :status="$claim->status" />
        </div>

        <dl class="grid sm:grid-cols-2 gap-4 mt-6 text-sm">
            <div class="rounded-xl bg-green-50 p-4">
                <dt class="text-slate-500">Tanggal klaim</dt>
                <dd class="font-semibold mt-1">{{ $claim->claim_date->format('d M Y') }}</dd>
            </div>
            @if ($claim->photo_taken_at)
                <div class="rounded-xl bg-green-50 p-4">
                    <dt class="text-slate-500">Foto diambil (EXIF)</dt>
                    <dd class="font-semibold mt-1">{{ $claim->photo_taken_at->format('d M Y H:i') }}</dd>
                </div>
            @endif
        </dl>

        {{-- PBI-18: alasan penolakan --}}
        @if ($claim->status === \App\Models\Claim::STATUS_REJECTED && $claim->cancel_reason)
            <div class="mt-6 rounded-xl bg-red-50 border border-red-200 p-4">
                <p class="font-semibold text-red-700">Alasan penolakan</p>
                <p class="text-red-700 mt-1">{{ $claim->cancel_reason }}</p>
            </div>
        @endif

        @if ($claim->note)
            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                <p class="font-semibold text-slate-600">Keterangan kamu</p>
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
    </article>

    {{-- PBI-18: kirim ulang bila ditolak --}}
    @if ($claim->status === \App\Models\Claim::STATUS_REJECTED)
        <article class="mt-6 bg-white rounded-2xl border border-green-100 shadow-sm p-6 sm:p-8">
            <h2 class="eco-h2">Kirim ulang bukti</h2>
            <p class="text-slate-600 mt-1 text-sm">Foto lama akan diganti dan status kembali menunggu verifikasi.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('claims.resubmit', $claim) }}" enctype="multipart/form-data" class="mt-4 space-y-5">
                @csrf

                <div>
                    <label for="photo" class="block text-sm font-semibold text-slate-700">Foto bukti baru</label>
                    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png"
                           class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-green-800 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-green-700">
                    <p class="text-xs text-slate-500 mt-1">Format JPG atau PNG, ukuran maksimal 2 MB.</p>
                </div>

                <div>
                    <label for="note" class="block text-sm font-semibold text-slate-700">Keterangan <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="note" name="note" rows="3" maxlength="300"
                              class="mt-2 block w-full rounded-xl border border-green-200 p-3 text-sm focus:border-green-600 focus:ring-green-600"
                              placeholder="Keterangan baru...">{{ old('note', $claim->note) }}</textarea>
                </div>

                <button type="submit" class="eco-btn rounded-lg bg-green-800 px-5 py-2.5 text-sm font-semibold hover:bg-green-700">
                    Kirim ulang
                </button>
            </form>
        </article>
    @endif
@endsection