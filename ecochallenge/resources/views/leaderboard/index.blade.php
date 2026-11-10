@extends('layouts.eco')

@section('title', 'Leaderboard')

@section('content')
    <div class="mb-6">
        <p class="text-xs font-semibold tracking-widest text-green-700">TUMBUH BERSAMA</p>
        <h1 class="eco-h1 mt-1">Papan aksi baik.</h1>
        <p class="text-slate-600 mt-1">Peringkat mahasiswa menurut total poin dari klaim yang disetujui. Nama yang tampil adalah username.</p>
    </div>

    @if ($myRank !== null)
        <div class="mb-6 rounded-2xl bg-green-800 text-white p-5 flex items-center justify-between" role="status">
            <div>
                <p class="text-xs tracking-widest opacity-80">POSISI KAMU</p>
                <p class="text-4xl font-bold">#{{ $myRank }}</p>
            </div>
            <p class="text-sm opacity-90 text-right">{{ auth()->user()->username }}<br>{{ number_format(auth()->user()->total_points) }} poin</p>
        </div>
    @endif

    @if (count($rows) === 0)
        <div class="bg-white rounded-2xl border border-green-100 p-10 text-center">
            <h2 class="eco-h2">Belum ada peringkat</h2>
            <p class="text-slate-600 mt-1">Peringkat muncul setelah ada mahasiswa yang klaimnya disetujui.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-green-100 overflow-x-auto">
            <table class="w-full text-sm" data-testid="leaderboard-table">
                <caption class="sr-only">{{ $limit }} peringkat teratas menurut total poin</caption>
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-slate-500 border-b border-green-100">
                        <th scope="col" class="px-4 py-3 w-16">Peringkat</th>
                        <th scope="col" class="px-4 py-3">Username</th>
                        <th scope="col" class="px-4 py-3 text-right">Poin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        @php $isMe = $row['id'] === $myId; @endphp
                        <tr class="border-b border-green-50 last:border-0 {{ $isMe ? 'bg-green-100 font-semibold' : '' }}"
                            @if ($isMe) data-me="true" aria-current="true" @endif>
                            <td class="px-4 py-3">{{ $row['rank'] }}</td>
                            <td class="px-4 py-3">
                                {{ $row['username'] }}
                                @if ($isMe)<span class="ml-2 text-xs font-medium text-green-800">· kamu</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($row['total_points']) }}</td>
                        </tr>
                    @endforeach

                    @if ($myRow)
                        <tr aria-hidden="true"><td colspan="3" class="px-4 py-1 text-center text-slate-400">⋮</td></tr>
                        <tr class="bg-green-100 font-semibold border-t border-green-200" data-me="true" aria-current="true">
                            <td class="px-4 py-3">{{ $myRow['rank'] }}</td>
                            <td class="px-4 py-3">{{ $myRow['username'] }}<span class="ml-2 text-xs font-medium text-green-800">· kamu</span></td>
                            <td class="px-4 py-3 text-right">{{ number_format($myRow['total_points']) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <p class="text-xs text-slate-500 mt-3">Menampilkan {{ $limit }} besar. Bila poin sama, urutan mengikuti username. Poin adalah ukuran partisipasi, bukan jumlah emisi yang dikurangi.</p>
    @endif
@endsection
