
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin EcoChallenge') - EcoChallenge</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8F7F2] text-stone-800 antialiased">
    <div class="min-h-screen flex flex-col md:flex-row">

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
                   class="block px-4 py-2.5 rounded-lg bg-[#1F6E4A] text-white font-semibold shadow-sm">
                    Kelola Challenge
                </a>
                <a href="{{ url('/admin/categories') }}"
                   class="block px-4 py-2.5 rounded-lg text-emerald-100/80 hover:bg-white/10 hover:text-white transition">
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

        <div class="flex-1 flex flex-col min-w-0">
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
                @yield('content')
            </main>
        </div>

    </div>
</body>
</html>
