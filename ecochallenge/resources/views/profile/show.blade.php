@extends('profile.layout')

@section('title', 'Profil')

@section('content')
<div class="profile-page">
    @if (session('status'))
        <div class="notice" role="status" dusk="profile-status">{{ session('status') }}</div>
    @endif

    <div class="profile-cover"><span>Setiap aksi kecil punya cerita.</span></div>

    <section class="profile-overview">
        @include('profile.partials.avatar', ['user' => $user])

        <h1 dusk="profile-name">{{ $user->name }}</h1>
        <span class="profile-handle" dusk="profile-username">{{ '@'.$user->username }}</span>

        <dl style="margin:20px 0;font-size:14px">
            <dt class="field-label" style="margin-top:0">Nama</dt>
            <dd style="margin:0">{{ $user->name }}</dd>

            <dt class="field-label">Username</dt>
            <dd style="margin:0">{{ $user->username }}</dd>

            <dt class="field-label">Email</dt>
            <dd style="margin:0" dusk="profile-email">{{ $user->email }}</dd>
        </dl>

        <a class="btn btn-primary" href="{{ route('profile.edit') }}" dusk="edit-profile">Edit profil</a>
    </section>
</div>
@endsection
