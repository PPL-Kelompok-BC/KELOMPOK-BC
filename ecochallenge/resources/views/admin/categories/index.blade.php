<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - EcoChallenge Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F6F7F2] text-slate-800 font-['Inter',sans-serif] antialiased">
    <div class="min-h-screen flex flex-col md:flex-row">
        {{-- Sidebar Navigasi Admin (Mockup M-13) --}}
        <aside class="w-full md:w-64 bg-[#1B4332] text-white flex-shrink-0">
            <div class="p-5 flex items-center justify-between md:justify-start gap-3 border-b border-emerald-800">
                <div class="flex items-center gap-2.5">
                    <span class="inline-block w-6 h-6 rounded-md bg-[#A3E635]"></span>
                    <span class="text-xl font-bold tracking-tight">eco</span>
                </div>
                <span class="text-xs uppercase tracking-wider bg-emerald-900 px-2.5 py-1 rounded-full md:hidden">
                    Admin
                </span>
            </div>

            <nav aria-label="Navigasi Admin" class="p-3 flex md:flex-col gap-1.5 overflow-x-auto">
                <a href="{{ url('/admin/dashboard') }}"
                   class="px-3.5 py-2.5 rounded-xl text-sm font-medium text-emerald-100 hover:bg-emerald-800/70 whitespace-nowrap transition">
                    Dashboard &amp; Laporan
                </a>
                <a href="{{ url('/admin/claims') }}"
                   class="px-3.5 py-2.5 rounded-xl text-sm font-medium text-emerald-100 hover:bg-emerald-800/70 whitespace-nowrap transition">
                    Tinjau Klaim
                </a>
                <a href="{{ route('admin.challenges.index') }}"
                   class="px-3.5 py-2.5 rounded-xl text-sm font-medium text-emerald-100 hover:bg-emerald-800/70 whitespace-nowrap transition">
                    Kelola Challenge
                </a>
                <a href="{{ route('admin.categories.index') }}"
                   aria-current="page"
                   class="px-3.5 py-2.5 rounded-xl text-sm font-semibold bg-emerald-700/80 text-white whitespace-nowrap">
                    Kelola Kategori
                </a>
                <a href="{{ url('/admin/users') }}"
                   class="px-3.5 py-2.5 rounded-xl text-sm font-medium text-emerald-100 hover:bg-emerald-800/70 whitespace-nowrap transition">
                    Kelola Akun
                </a>
                <a href="{{ url('/admin/streak-freezes') }}"
                   class="px-3.5 py-2.5 rounded-xl text-sm font-medium text-emerald-100 hover:bg-emerald-800/70 whitespace-nowrap transition">
                    Tanggal Freeze
                </a>
            </nav>
        </aside>

        {{-- Konten Utama --}}
        <div class="flex-1 flex flex-col">
            <header class="bg-white/80 backdrop-blur border-b border-slate-200/80 px-6 py-4 flex items-center justify-between">
                <span class="text-sm font-medium text-slate-600">Admin</span>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-700 hidden sm:inline">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <span class="w-8 h-8 rounded-full bg-[#D9F99D] text-[#1B4332] font-bold text-xs flex items-center justify-center">
                        AD
                    </span>
                </div>
            </header>

            <main class="p-6 md:p-8 max-w-6xl w-full mx-auto">
                <div class="mb-6">
                    <p class="text-xs font-semibold tracking-wider uppercase text-emerald-800 mb-1">
                        MASTER DATA
                    </p>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                        Kelola kategori
                    </h1>
                </div>

                {{-- Tabel Daftar Kategori (PBI-05) --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/70 text-xs font-semibold uppercase tracking-wider text-slate-600">
                                    <th class="py-3.5 px-6">Kategori</th>
                                    <th class="py-3.5 px-6">Jumlah Challenge</th>
                                    <th class="py-3.5 px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @forelse ($categories as $category)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-6">
                                            <div class="font-semibold text-slate-900">{{ $category->name }}</div>
                                            @if ($category->description)
                                                <p class="text-xs text-slate-500 mt-0.5">{{ $category->description }}</p>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 font-medium text-slate-700">
                                            {{ $category->challenges_count }}
                                        </td>
                                        <td class="py-4 px-6 text-right space-x-3 text-xs font-medium text-slate-400">
                                            <span>Ubah</span>
                                            <span>Hapus</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-8 px-6 text-center text-slate-500">
                                            Belum ada kategori challenge yang tersedia.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-3.5 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-500">
                        Kategori yang masih punya challenge tidak bisa dihapus.
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>