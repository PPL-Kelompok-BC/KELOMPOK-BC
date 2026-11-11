<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#143b2e">
    <title>@yield('title') | EcoChallenge</title>
    <style>[x-cloak]{display:none !important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-slate-800 antialiased selection:bg-lime-eco selection:text-eco-900">
<div class="grid min-h-screen lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">

    {{-- Panel merek (layar besar) --}}
    <aside class="relative hidden overflow-hidden bg-gradient-to-br from-eco-950 via-eco-900 to-eco-700 text-white lg:flex lg:flex-col lg:justify-between lg:p-12" aria-hidden="false">
        <div class="pointer-events-none absolute inset-0 opacity-[0.14]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>
        <div class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-lime-eco/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 size-[26rem] rounded-full bg-eco-400/25 blur-3xl"></div>

        <a href="{{ url('/') }}" class="relative z-10 flex w-fit items-center gap-2.5 rounded-lg outline-none focus-visible:ring-2 focus-visible:ring-lime-eco">
            <span class="grid size-10 place-items-center rounded-xl bg-lime-eco text-eco-900 shadow"><x-ui.icon name="leaf" class="size-5" /></span>
            <span class="text-xl font-bold tracking-tight">eco<span class="font-normal text-eco-200">challenge</span></span>
        </a>

        <div class="relative z-10 my-10">
            <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-wider text-lime-eco">
                <x-ui.icon name="globe" class="size-3.5" /> SDG 13 · CLIMATE ACTION
            </p>
            <h2 class="text-5xl font-bold leading-[1.05] tracking-tight xl:text-6xl">
                Aksi kecil.<br>Kebiasaan baik.<br><span class="text-lime-eco">Bareng kamu.</span>
            </h2>
            <p class="mt-5 max-w-md text-base leading-relaxed text-eco-100">
                Ikuti challenge ramah lingkungan, kirim bukti, kumpulkan poin, dan naik peringkat bersama teman satu kampus.
            </p>

            <ul class="mt-8 grid gap-3 text-sm text-eco-50">
                <li class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-white/10"><x-ui.icon name="target" class="size-4 text-lime-eco" /></span> Pilih aksi yang cocok dengan harimu</li>
                <li class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-white/10"><x-ui.icon name="camera" class="size-4 text-lime-eco" /></span> Kirim foto bukti, ditinjau admin</li>
                <li class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-white/10"><x-ui.icon name="trophy" class="size-4 text-lime-eco" /></span> Naik peringkat dengan username-mu</li>
            </ul>
        </div>

        {{-- Kartu mengambang dekoratif --}}
        <div class="pointer-events-none absolute bottom-24 right-8 hidden w-60 xl:block" aria-hidden="true">
            <div class="animate-float rounded-2xl bg-white p-4 text-eco-900 shadow-2xl" style="--r:-4deg">
                <p class="text-[0.65rem] font-semibold tracking-widest text-slate-400">AKSI DISETUJUI</p>
                <div class="mt-1.5 flex items-center justify-between">
                    <span class="text-sm font-semibold">Bawa tumbler sendiri</span>
                    <span class="rounded-full bg-lime-eco px-2 py-0.5 text-xs font-bold">+20</span>
                </div>
                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-eco-100"><div class="h-full w-2/3 rounded-full bg-eco-600"></div></div>
            </div>
            <div class="animate-float-slow mt-3 ml-10 flex items-center gap-3 rounded-2xl bg-white/95 p-3 pr-5 text-eco-900 shadow-xl" style="--r:3deg">
                <span class="grid size-10 place-items-center rounded-xl bg-amber-100 text-amber-600"><x-ui.icon name="flame" class="size-5" /></span>
                <div><p class="text-lg font-bold leading-none">5 hari</p><p class="text-xs text-slate-500">streak aktif</p></div>
            </div>
        </div>

        <p class="relative z-10 text-xs text-eco-200">Proyek PPL Kelompok B-C · Telkom University</p>
    </aside>

    {{-- Kolom formulir --}}
    <div class="flex min-w-0 flex-col">
        {{-- Header ponsel --}}
        <header class="relative overflow-hidden bg-gradient-to-br from-eco-950 via-eco-900 to-eco-700 px-5 pb-10 pt-6 text-white lg:hidden">
            <div class="pointer-events-none absolute -right-10 -top-10 size-44 rounded-full bg-lime-eco/20 blur-2xl"></div>
            <div class="relative flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <span class="grid size-9 place-items-center rounded-xl bg-lime-eco text-eco-900"><x-ui.icon name="leaf" class="size-5" /></span>
                    <span class="text-lg font-bold tracking-tight">eco<span class="font-normal text-eco-200">challenge</span></span>
                </a>
                @yield('top-link')
            </div>
            <p class="relative mt-6 text-2xl font-bold leading-tight tracking-tight">Aksi kecil. <span class="text-lime-eco">Bareng kamu.</span></p>
        </header>

        <div class="hidden justify-end px-12 pt-8 lg:flex">@yield('top-link')</div>

        <main id="main" class="-mt-6 flex flex-1 items-start justify-center px-4 pb-10 sm:px-6 lg:mt-0 lg:items-center lg:px-12 lg:py-10">
            <div class="w-full max-w-md animate-fade-up rounded-3xl bg-white p-6 shadow-lift ring-1 ring-black/5 sm:p-9 lg:bg-transparent lg:p-0 lg:shadow-none lg:ring-0">
                @yield('content')
            </div>
        </main>

        <footer class="px-6 pb-6 text-center text-xs text-slate-400 lg:text-left lg:px-12">
            EcoChallenge · Proyek PPL Kelompok B-C · SDG 13
        </footer>
    </div>
</div>
</body>
</html>
