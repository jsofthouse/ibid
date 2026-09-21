<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterKategoriRequest extends FormRequest
{
    public const DAFTAR_URUTKAN = ['nama', 'created_at'];

    public const DAFTAR_ARAH = ['asc', 'desc'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cari' => ['nullable', 'string', 'max:255'],
            'urutkan' => ['nullable', 'string', Rule::in(self::DAFTAR_URUTKAN)],
            'arah' => ['nullable', 'string', Rule::in(self::DAFTAR_ARAH)],
        ];
    }
}
