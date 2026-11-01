<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#235941">
<title>@yield('title', 'Profil') • EcoChallenge</title>
@vite(['resources/css/app.css'])
</head>
<body>
<header class="topbar">
    <div class="wrap">
        <a class="brand" href="{{ url('/') }}">eco<span style="font-weight:400;margin-left:-8px">challenge</span></a>
        <nav aria-label="Navigasi profil">
            <a class="text-btn" href="{{ route('profile.show') }}">Profilku</a>
        </nav>
    </div>
</header>

<main id="main" class="wrap" style="padding-bottom:48px">
    @yield('content')
</main>
</body>
</html>
