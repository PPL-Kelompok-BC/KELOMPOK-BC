<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approve,reject'],
            'cancel_reason' => ['required_if:decision,reject', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Pilih keputusan terlebih dahulu.',
            'decision.in' => 'Keputusan harus approve atau reject.',
            'cancel_reason.required_if' => 'Alasan pembatalan wajib diisi saat klaim ditolak.',
            'cancel_reason.max' => 'Alasan pembatalan maksimal 255 karakter.',
        ];
    }
}
