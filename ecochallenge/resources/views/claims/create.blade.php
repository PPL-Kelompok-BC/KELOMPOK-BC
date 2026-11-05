@extends('layouts.eco')

@section('title', 'Kirim Bukti')

@section('content')
    <a href="{{ route('challenges.show', $challenge) }}" class="text-sm text-green-800 hover:underline">← Kembali ke challenge</a>

    <article class="mt-4 bg-white rounded-2xl border border-green-100 shadow-sm p-6 sm:p-8">
        <h1 class="eco-h1">Kirim bukti</h1>
        <p class="text-slate-600 mt-1">{{ $challenge->title }}</p>

        <div class="mt-4 rounded-xl bg-green-50 p-4 text-sm">
            <p class="font-semibold text-green-900">Kriteria bukti</p>
            <p class="text-slate-700 mt-1">{{ $challenge->proof_criteria ?: 'Kriteria bukti untuk challenge ini belum diisi.' }}</p>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('claims.store', $challenge) }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="photo" class="block text-sm font-semibold text-slate-700">Foto bukti</label>
                <input id="photo" name="photo" type="file" accept="image/jpeg,image/png"
                       class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-green-800 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-green-700">
                <p class="text-xs text-slate-500 mt-1">Format JPG atau PNG, ukuran maksimal 2 MB. Foto disimpan apa adanya.</p>
            </div>

            <div>
                <label for="note" class="block text-sm font-semibold text-slate-700">Keterangan <span class="font-normal text-slate-400">(opsional)</span></label>
                <textarea id="note" name="note" rows="3" maxlength="300"
                          class="mt-2 block w-full rounded-xl border border-green-200 p-3 text-sm focus:border-green-600 focus:ring-green-600"
                          placeholder="Ceritakan singkat kegiatanmu...">{{ old('note') }}</textarea>
            </div>

            <button type="submit" class="eco-btn rounded-lg bg-green-800 px-5 py-2.5 text-sm font-semibold hover:bg-green-700">
                Kirim bukti
            </button>
        </form>
    </article>
@endsection