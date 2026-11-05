@extends('profile.layout')

@section('title', 'Edit profil')

@section('content')
<div class="profile-page">
    <a class="text-btn" href="{{ route('profile.show') }}">← Kembali ke profil</a>

    <div class="profile-cover"><span>Perbarui data dirimu.</span></div>

    <section class="profile-overview">
        @include('profile.partials.avatar', ['user' => $user])

        <h1>Edit profil</h1>

        <form method="POST" action="{{ route('profile.update') }}" class="profile-edit" novalidate>
            @csrf
            @method('PATCH')

            <label class="field-label" for="name">Nama</label>
            <input id="name" name="name" type="text" class="field" maxlength="255" required
                   value="{{ old('name', $user->name) }}" autocomplete="name" dusk="name">
            @error('name') <div class="error" role="alert">{{ $message }}</div> @enderror

            <label class="field-label" for="username">Username</label>
            <input id="username" name="username" type="text" class="field" minlength="3" maxlength="30" required
                   value="{{ old('username', $user->username) }}" autocomplete="username" dusk="username">
            <small class="profile-handle" style="display:block;margin-top:6px">Huruf kecil, angka, titik, dan garis bawah. Tampil di leaderboard.</small>
            @error('username') <div class="error" role="alert">{{ $message }}</div> @enderror

            <label class="field-label" for="email">Email</label>
            <input id="email" name="email" type="email" class="field" maxlength="255" required
                   value="{{ old('email', $user->email) }}" autocomplete="email" dusk="email">
            @error('email') <div class="error" role="alert">{{ $message }}</div> @enderror

            <button type="submit" class="btn btn-primary" style="margin-top:18px" dusk="save-profile">Simpan profil</button>
        </form>

        <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data"
              class="profile-edit" style="margin-top:32px">
            @csrf

            <label class="field-label" for="photo">Foto profil</label>
            <input id="photo" name="photo" type="file" class="field" accept="image/jpeg,image/png" required dusk="photo">
            <small class="profile-handle" style="display:block;margin-top:6px">JPG atau PNG, maksimal 2 MB. Foto otomatis dikompres.</small>
            @error('photo') <div class="error" role="alert">{{ $message }}</div> @enderror

            <button type="submit" class="btn" style="margin-top:18px" dusk="save-photo">Unggah foto</button>
        </form>
    </section>
</div>
@endsection
