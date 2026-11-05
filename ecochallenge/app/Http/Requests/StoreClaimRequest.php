<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'note' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Pilih foto bukti terlebih dahulu.',
            'photo.file' => 'Foto gagal diunggah. Coba lagi.',
            'photo.uploaded' => 'Foto gagal diunggah. Ukuran maksimal 2 MB.',
            'photo.image' => 'Foto bukti harus berformat JPG atau PNG.',
            'photo.mimes' => 'Foto bukti harus berformat JPG atau PNG.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'note.max' => 'Keterangan maksimal 300 karakter.',
        ];
    }
}
