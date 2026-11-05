@php
    $initials = collect(preg_split('/\s+/', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

<span class="profile-avatar" style="background:var(--lime);color:var(--green);overflow:hidden">
    @if ($user->profile_photo_path)
        <img
            src="{{ route('profile.photo', $user) }}?v={{ md5($user->profile_photo_path) }}"
            alt="Foto profil {{ $user->name }}"
            width="105" height="105"
            style="width:100%;height:100%;object-fit:cover"
            dusk="profile-photo"
        >
    @else
        <span aria-hidden="true">{{ $initials ?: '?' }}</span>
    @endif
</span>
