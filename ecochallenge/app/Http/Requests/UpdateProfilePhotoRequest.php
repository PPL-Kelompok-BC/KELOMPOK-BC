<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'photo' => [
                'required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048',
                'dimensions:max_width=6000,max_height=6000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Pilih foto terlebih dahulu.',
            'photo.file' => 'Foto gagal diunggah. Coba lagi.',
            'photo.uploaded' => 'Foto gagal diunggah. Ukuran maksimal 2 MB.',
            'photo.image' => 'Foto harus berformat JPG atau PNG.',
            'photo.mimes' => 'Foto harus berformat JPG atau PNG.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'photo.dimensions' => 'Resolusi foto terlalu besar. Maksimal 6000 x 6000 piksel.',
        ];
    }
}
