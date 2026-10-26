<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#235941">
<title>@yield('title') • EcoChallenge</title>
@vite(['resources/css/app.css'])
</head>
<body>
<header class="topbar"><div class="wrap">
<a class="brand" href="{{ url('/') }}"><span class="brand-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg></span>eco<span style="font-weight:400;margin-left:-8px">challenge</span></a>
<div class="navright">@yield('nav')</div>
</div></header>
<main class="wrap auth-page">@yield('content')</main>
<footer class="footer"><div class="wrap"><span>EcoChallenge · Proyek PPL Kelompok B-C · SDG 13</span></div></footer>
</body>
</html>
