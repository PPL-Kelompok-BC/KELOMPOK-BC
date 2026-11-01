<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'username' => strtolower(trim((string) $this->input('username'))),
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        $userId = $this->user()->getKey();

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9_.]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'username' => 'username',
            'email' => 'email',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'max' => ':Attribute maksimal :max karakter.',
            'username.min' => 'Username minimal :min karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, titik, dan garis bawah.',
            'username.unique' => 'Username sudah dipakai. Pilih username lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah dipakai akun lain.',
        ];
    }
}
