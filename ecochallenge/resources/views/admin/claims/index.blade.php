@extends('layouts.eco')

@section('title', 'Tinjau Klaim')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-semibold tracking-widest text-green-700">
                ADMINISTRATOR
            </p>

            <h1 class="eco-h1 mt-1">Tinjau Klaim</h1>

            <p class="text-slate-600 mt-1">
                Periksa bukti foto mahasiswa dan tandai sesuai atau batalkan.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-green-100 bg-white shadow-sm">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="bg-green-50 text-green-800">
                <tr>
                    <th class="px-4 py-3 font-semibold">Mahasiswa</th>
                    <th class="px-4 py-3 font-semibold">Challenge</th>
                    <th class="px-4 py-3 font-semibold">Tanggal</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-green-100">
                @forelse ($claims as $claim)
                    <tr class="hover:bg-green-50/40">
                        <td class="px-4 py-3">{{ $claim->user->name }}</td>
                        <td class="px-4 py-3">{{ $claim->challenge->title }}</td>
                        <td class="px-4 py-3">{{ $claim->claim_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <x-claim-status :status="$claim->status" />
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.claims.show', $claim) }}"
                               class="inline-flex items-center rounded-lg bg-green-800 px-4 py-2 text-xs font-semibold text-white hover:bg-green-900">
                                @if ($claim->status === \App\Models\Claim::STATUS_PENDING) Tinjau @else Lihat @endif
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                            Belum ada klaim.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
