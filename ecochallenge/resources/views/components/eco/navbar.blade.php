@php
    $user = auth()->user();

    // Menu hanya tampil bila route-nya sudah dibuat anggota lain.
    $menu = collect([
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Challenge', 'route' => 'challenges.index', 'icon' => 'target'],
        ['label' => 'Klaim', 'route' => 'claims.index', 'icon' => 'camera'],
        ['label' => 'Leaderboard', 'route' => 'leaderboard.index', 'icon' => 'trophy'],
        ['label' => 'Riwayat', 'route' => 'history.index', 'icon' => 'clipboard-list'],
    ]);

    if ($user?->isAdmin()) {
        $menu->push(['label' => 'Admin', 'route' => 'admin.dashboard', 'icon' => 'shield-check']);
    }

    $menu = $menu->filter(fn ($item) => Route::has($item['route']));

    $photo = $user?->profile_photo_path && Route::has('profile.photo')
        ? route('profile.photo', $user).'?v='.md5($user->profile_photo_path)
        : null;
@endphp

<header x-data="{ open: false, account: false }"
        x-on:keydown.escape.window="open = false; account = false"
        class="sticky top-0 z-40 border-b border-white/10 bg-eco-900/95 text-white shadow-lg shadow-eco-950/10 backdrop-blur supports-[backdrop-filter]:bg-eco-900/85">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">
        <a href="{{ $user ? (Route::has('dashboard') ? route('dashboard') : url('/')) : url('/') }}"
           class="group flex items-center gap-2.5 rounded-lg outline-none focus-visible:ring-2 focus-visible:ring-lime-eco">
            <span class="grid size-9 place-items-center rounded-xl bg-lime-eco text-eco-900 shadow-sm transition group-hover:rotate-[-6deg]">
                <x-ui.icon name="leaf" class="size-5" />
            </span>
            <span class="text-lg font-bold tracking-tight">eco<span class="font-normal text-eco-200">challenge</span></span>
        </a>

        <nav class="ml-2 hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
            @foreach ($menu as $item)
                @php $active = request()->routeIs($item['route'].'*'); @endphp
                <a href="{{ route($item['route']) }}"
                   @if ($active) aria-current="page" @endif
                   class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-medium outline-none transition focus-visible:ring-2 focus-visible:ring-lime-eco {{ $active ? 'bg-white/15 text-white' : 'text-eco-100 hover:bg-white/10 hover:text-white' }}">
                    <x-ui.icon :name="$item['icon']" class="size-4 opacity-80" />
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2">
            @auth
                {{-- Menu akun (desktop) --}}
                <div class="relative hidden md:block" x-on:click.outside="account = false">
                    <button type="button" x-on:click="account = !account" :aria-expanded="account" aria-haspopup="menu"
                            class="flex items-center gap-2 rounded-full border border-white/15 bg-white/5 py-1 pl-1 pr-3 text-sm outline-none transition hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-lime-eco">
                        <x-ui.avatar :name="$user->name" :src="$photo" :seed="$user->username" class="size-8 text-xs" />
                        <span class="max-w-[9rem] truncate font-medium">{{ $user->username }}</span>
                        <x-ui.icon name="chevron-down" class="size-4 opacity-70 transition" x-bind:class="account ? 'rotate-180' : ''" />
                    </button>

                    <div x-show="account" x-cloak x-transition.origin.top.right
                         class="absolute right-0 mt-2 w-64 overflow-hidden rounded-2xl bg-white text-slate-800 shadow-xl ring-1 ring-black/5"
                         role="menu">
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-eco-50 px-4 py-3.5">
                            <x-ui.avatar :name="$user->name" :src="$photo" :seed="$user->username" class="size-11" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-eco-900">{{ $user->name }}</p>
                                <p class="truncate text-xs text-slate-500">&#64;{{ $user->username }} · {{ $user->isAdmin() ? 'Admin' : 'Mahasiswa' }}</p>
                            </div>
                        </div>
                        <div class="p-1.5">
                            @if (Route::has('profile.show'))
                                <a href="{{ route('profile.show') }}" role="menuitem"
                                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 outline-none transition hover:bg-eco-50 focus-visible:bg-eco-50">
                                    <x-ui.icon name="user-round" class="size-4 text-eco-700" /> Profil saya
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" role="menuitem"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm text-rose-700 outline-none transition hover:bg-rose-50 focus-visible:bg-rose-50">
                                    <x-ui.icon name="log-out" class="size-4" /> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="hidden rounded-full px-4 py-2 text-sm font-medium transition hover:bg-white/10 sm:inline-flex">Masuk</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex rounded-full bg-lime-eco px-4 py-2 text-sm font-semibold text-eco-900 transition hover:brightness-95">Daftar</a>
                @endif
            @endauth

            <button type="button" x-on:click="open = !open" :aria-expanded="open" aria-controls="menu-ponsel" aria-label="Buka menu navigasi"
                    class="grid size-10 place-items-center rounded-xl border border-white/15 outline-none transition hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-lime-eco md:hidden">
                <x-ui.icon name="menu" class="size-5" x-show="!open" />
                <x-ui.icon name="x" class="size-5" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    {{-- Menu ponsel --}}
    <nav id="menu-ponsel" x-show="open" x-cloak x-transition.opacity.duration.150ms
         class="border-t border-white/10 bg-eco-900 px-4 pb-5 pt-3 md:hidden" aria-label="Navigasi ponsel">
        @auth
            <div class="mb-3 flex items-center gap-3 rounded-2xl bg-white/5 p-3">
                <x-ui.avatar :name="$user->name" :src="$photo" :seed="$user->username" class="size-11" />
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                    <p class="truncate text-xs text-eco-200">&#64;{{ $user->username }} · {{ $user->isAdmin() ? 'Admin' : 'Mahasiswa' }}</p>
                </div>
            </div>
        @endauth

        <div class="grid gap-1">
            @foreach ($menu as $item)
                @php $active = request()->routeIs($item['route'].'*'); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-3 text-[0.95rem] font-medium transition {{ $active ? 'bg-white/15' : 'hover:bg-white/10' }}">
                    <x-ui.icon :name="$item['icon']" class="size-5 text-lime-eco" /> {{ $item['label'] }}
                </a>
            @endforeach
            @auth
                @if (Route::has('profile.show'))
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-[0.95rem] font-medium transition hover:bg-white/10">
                        <x-ui.icon name="user-round" class="size-5 text-lime-eco" /> Profil saya
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="mt-2 border-t border-white/10 pt-3">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-[0.95rem] font-medium text-rose-200 transition hover:bg-white/10">
                        <x-ui.icon name="log-out" class="size-5" /> Keluar
                    </button>
                </form>
            @endauth
        </div>
    </nav>
</header>
