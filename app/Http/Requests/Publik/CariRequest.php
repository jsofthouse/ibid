<?php

namespace App\Http\Requests\Publik;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CariRequest extends FormRequest
{
    public const DAFTAR_URUTKAN = ['judul', 'tanggal_dipublikasikan'];

    public const DAFTAR_ARAH = ['asc', 'desc'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'urutkan' => ['nullable', 'string', Rule::in(self::DAFTAR_URUTKAN)],
            'arah' => ['nullable', 'string', Rule::in(self::DAFTAR_ARAH)],
        ];
    }
}
