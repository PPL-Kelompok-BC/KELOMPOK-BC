<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidProfilePhotoException;
use App\Http\Requests\UpdateProfilePhotoRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Services\ProfilePhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfilePhotoService $photos) {}

    public function show(Request $request): View
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated())->save();

        return redirect()
            ->route('profile.show')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePhoto(UpdateProfilePhotoRequest $request): RedirectResponse
    {
        try {
            $this->photos->replace($request->user(), $request->file('photo'));
        } catch (InvalidProfilePhotoException $exception) {
            return redirect()
                ->route('profile.edit')
                ->withErrors(['photo' => $exception->getMessage()]);
        }

        return redirect()
            ->route('profile.show')
            ->with('status', 'Foto profil berhasil diperbarui.');
    }

    public function photo(User $user): StreamedResponse
    {
        $path = $user->profile_photo_path;
        $disk = Storage::disk(ProfilePhotoService::DISK);

        abort_if(! $path || ! $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
