
@extends('layouts.eco')

@section('title', 'Kelola Challenge')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-semibold tracking-widest text-green-700">
                ADMINISTRATOR
            </p>

            <h1 class="eco-h1 mt-1">Kelola Challenge</h1>

            <p class="text-slate-600 mt-1">
                Lihat dan pantau seluruh challenge yang tersedia.
            </p>
        </div>

        {{-- PBI-09: Tombol tambah challenge --}}
        <a href="{{ route('admin.challenges.create') }}"
           class="inline-flex items-center rounded-xl bg-green-800 px-5 py-3 text-sm font-semibold text-white hover:bg-green-900">
            + Tambah Challenge
        </a>
    </div>

    {{-- Notifikasi berhasil --}}
    @if (session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800"
             role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter status challenge --}}
    <div class="flex flex-wrap gap-2 mb-6" aria-label="Filter status">
        <a href="{{ route('admin.challenges.index', ['status' => 'all']) }}"
           class="px-4 py-2 rounded-full text-sm border
           {{ $status === 'all' ? 'bg-green-800 text-white border-green-800' : 'bg-white border-green-200 hover:bg-green-100' }}">
            Semua
        </a>

        <a href="{{ route('admin.challenges.index', ['status' => 'active']) }}"
           class="px-4 py-2 rounded-full text-sm border
           {{ $status === 'active' ? 'bg-green-800 text-white border-green-800' : 'bg-white border-green-200 hover:bg-green-100' }}">
            Aktif
        </a>

        <a href="{{ route('admin.challenges.index', ['status' => 'inactive']) }}"
           class="px-4 py-2 rounded-full text-sm border
           {{ $status === 'inactive' ? 'bg-green-800 text-white border-green-800' : 'bg-white border-green-200 hover:bg-green-100' }}">
            Nonaktif
        </a>
    </div>

    <p class="text-sm text-slate-600 mb-4" aria-live="polite">
        Menampilkan {{ $challenges->count() }} challenge
    </p>

    {{-- Daftar challenge --}}
    @if ($challenges->isEmpty())
        <div class="bg-white rounded-2xl border border-green-100 p-10 text-center">
            <h2 class="eco-h2">Belum ada challenge</h2>

            <p class="text-slate-600 mt-1">
                Tidak ditemukan challenge untuk status yang dipilih.
            </p>
        </div>
    @else
        <div class="overflow-x-auto bg-white rounded-2xl border border-green-100 shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-green-100 text-green-900">
                    <tr>
                        <th class="px-4 py-3">No.</th>
                        <th class="px-4 py-3">Nama Challenge</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Kesulitan</th>
                        <th class="px-4 py-3">Poin</th>
                        <th class="px-4 py-3">Estimasi Emisi</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-green-100">
                    @foreach ($challenges as $challenge)
                        <tr class="hover:bg-green-50">
                            <td class="px-4 py-3">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-4 py-3 font-medium">
                                {{ $challenge->title }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $challenge->category->name }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $challenge->difficulty }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $challenge->points }} poin
                            </td>

                            <td class="px-4 py-3">
                                {{ number_format($challenge->estimated_co2e_gram) }} g CO2e
                            </td>

                            <td class="px-4 py-3">
                                @if ($challenge->is_active)
                                    <span class="inline-block rounded-full bg-green-100 text-green-800 px-3 py-1 text-xs font-semibold">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block rounded-full bg-red-100 text-red-800 px-3 py-1 text-xs font-semibold">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
