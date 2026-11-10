<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#143b2e">
    <title>@yield('title', 'EcoChallenge') | EcoChallenge</title>
    <style>
        [x-cloak]{display:none !important}
        /* Dipertahankan agar halaman anggota lain yang memakai kelas ini tetap tampil sama. */
        .eco-h1{font-size:1.875rem !important;line-height:2.25rem !important;font-weight:700 !important;letter-spacing:normal !important}
        .eco-h2{font-size:1.125rem !important;line-height:1.5rem !important;font-weight:600 !important;letter-spacing:normal !important}
        .eco-btn{color:#fff !important;text-decoration:none !important}
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-paper text-slate-800 antialiased selection:bg-lime-eco selection:text-eco-900">
    <a href="#main" class="sr-only rounded-lg bg-white px-3 py-2 text-eco-900 shadow focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-50">Lewati navigasi</a>

    <x-eco.navbar />

    <main id="main" class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 sm:py-10">
        @yield('content')
    </main>

    <x-eco.footer />
</body>
</html>
