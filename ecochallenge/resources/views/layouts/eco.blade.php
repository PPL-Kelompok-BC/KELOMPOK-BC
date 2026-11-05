<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EcoChallenge') | EcoChallenge</title>
    <style>
        [x-cloak]{display:none !important}
        /* Menimpa gaya global GUI demo (app.css) agar tidak merusak halaman Blade ini */
        .eco-h1{font-size:1.875rem !important;line-height:2.25rem !important;font-weight:700 !important;letter-spacing:normal !important}
        .eco-h2{font-size:1.125rem !important;line-height:1.5rem !important;font-weight:600 !important;letter-spacing:normal !important}
        .eco-btn{color:#fff !important;text-decoration:none !important}
        .eco-nav a{color:#fff !important;text-decoration:none !important}
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-green-50 text-slate-800 antialiased flex flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 bg-white px-3 py-2 rounded">Lewati navigasi</a>

    <x-eco.navbar />

    <main id="main" class="flex-1 w-full max-w-6xl mx-auto px-4 sm:px-6 py-8">
        @yield('content')
    </main>

    <x-eco.footer />
</body>
</html>
