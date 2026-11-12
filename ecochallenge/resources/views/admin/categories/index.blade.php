<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - EcoChallenge Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8F7F2] text-stone-800 antialiased">
    <div class="min-h-screen flex flex-col md:flex-row">
        {{-- Sidebar Kiri --}}
        <aside class="w-full md:w-64 bg-[#143D29] text-white flex-shrink-0 flex flex-col">
            <div class="px-6 py-5 flex items-center gap-3 border-b border-white/10">
                <div class="w-7 h-7 rounded-md bg-[#74C69D] flex items-center justify-center">
                    <span class="w-3 h-3 rounded-sm bg-[#143D29]"></span>
                </div>
                <span class="text-lg font-bold tracking-tight">eco</span>
            </div>

            <nav class="flex-1 px-4 py-5 space-y-1.5 text-sm">
                <a href="{{ url('/admin/dashboard') }}"
                   class="block px-4 py-2.5 rounded-lg text-emerald-100/80 hover:bg-white/10 hover:text-white transition">
                    Dashboard & Laporan
                </a>
                <a href="{{ url('/admin/claims') }}"
                   class="block px-4 py-2.5 rounded-lg text-emerald-100/80 hover:bg-white/10 hover:text-white transition">
                    Tinjau Klaim
                </a>
                <a href="{{ url('/admin/challenges') }}"
                   class="block px-4 py-2.5 rounded-lg text-emerald-100/80 hover:bg-white/10 hover:text-white transition">
                    Kelola Challenge
                </a>
                <a href="{{ url('/admin/categories') }}"
                   class="block px-4 py-2.5 rounded-lg bg-[#1F6E4A] text-white font-semibold shadow-sm">
                    Kelola Kategori
                </a>
                <a href="{{ url('/admin/users') }}"
                   class="block px-4 py-2.5 rounded-lg text-emerald-100/80 hover:bg-white/10 hover:text-white transition">
                    Kelola Akun
                </a>
                <a href="{{ url('/admin/freeze-dates') }}"
                   class="block px-4 py-2.5 rounded-lg text-emerald-100/80 hover:bg-white/10 hover:text-white transition">
                    Tanggal Freeze
                </a>
            </nav>
        </aside>

        {{-- Konten Utama --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Header Atas --}}
            <header class="bg-[#F8F7F2] border-b border-stone-200 px-6 md:px-10 py-4 flex items-center justify-between text-xs text-stone-600">
                <span class="font-medium">Admin</span>
                <div class="flex items-center gap-2.5">
                    <span>Admin EcoChallenge</span>
                    <span class="w-7 h-7 rounded-full bg-emerald-100 text-[#143D29] font-bold flex items-center justify-center text-[11px]">
                        AD
                    </span>
                </div>
            </header>

            <main class="flex-1 px-6 md:px-10 py-8 max-w-5xl w-full mx-auto space-y-6">
                <div>
                    <p class="text-[11px] font-semibold tracking-wider uppercase text-[#2D6A4F]">
                        MASTER DATA
                    </p>
                    <h1 class="text-2xl font-bold text-stone-900 mt-0.5">
                        Kelola kategori
                    </h1>
                </div>

                {{-- Notifikasi Berhasil --}}
                @if (session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                {{-- Notifikasi Error Validasi --}}
                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form Tambah Kategori (PBI-06) --}}
                <section class="bg-white rounded-2xl border border-stone-200/90 shadow-xs p-5 md:p-6">
                    <h2 class="text-sm font-semibold text-stone-900 mb-4">Tambah kategori baru</h2>
                    <form method="POST" action="{{ url('/admin/categories') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        @csrf
                        <div class="md:col-span-4">
                            <label for="name" class="block text-xs font-medium text-stone-700 mb-1">
                                Nama Kategori <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Contoh: Konservasi Air"
                                class="w-full rounded-lg border border-stone-300 bg-white px-3.5 py-2 text-sm text-stone-800 placeholder:text-stone-400 focus:border-[#1F6E4A] focus:outline-none focus:ring-2 focus:ring-[#1F6E4A]/20"
                            >
                        </div>

                        <div class="md:col-span-5">
                            <label for="description" class="block text-xs font-medium text-stone-700 mb-1">
                                Deskripsi
                            </label>
                            <input
                                type="text"
                                id="description"
                                name="description"
                                value="{{ old('description') }}"
                                placeholder="Deskripsi singkat kategori (opsional)"
                                class="w-full rounded-lg border border-stone-300 bg-white px-3.5 py-2 text-sm text-stone-800 placeholder:text-stone-400 focus:border-[#1F6E4A] focus:outline-none focus:ring-2 focus:ring-[#1F6E4A]/20"
                            >
                        </div>

                        <div class="md:col-span-3">
                            <button
                                type="submit"
                                class="w-full rounded-lg bg-[#143D29] hover:bg-[#1F6E4A] text-white text-sm font-semibold px-4 py-2 transition cursor-pointer"
                            >
                                + Tambah Kategori
                            </button>
                        </div>
                    </form>
                </section>

                {{-- Tabel Daftar Kategori --}}
                <section class="bg-white rounded-2xl border border-stone-200/90 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-stone-200 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                                    <th class="px-6 py-4">KATEGORI</th>
                                    <th class="px-6 py-4">JUMLAH CHALLENGE</th>
                                    <th class="px-6 py-4 text-right">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 text-sm">
                                @forelse ($categories as $category)
                                    <tr class="hover:bg-stone-50/70 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-stone-900">{{ $category->name }}</div>
                                            @if ($category->description)
                                                <div class="text-xs text-stone-500 mt-0.5">{{ $category->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-stone-800">
                                            {{ $category->challenges_count }}
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-3 text-xs font-medium text-stone-400">
                                            <span>Ubah</span>
                                            <span>Hapus</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-sm text-stone-500">
                                            Belum ada kategori terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-3.5 border-t border-stone-100 bg-stone-50/50 text-xs text-stone-500">
                        Kategori yang masih punya challenge tidak bisa dihapus.
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>