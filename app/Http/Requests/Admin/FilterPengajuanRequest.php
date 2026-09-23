<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusPengajuan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterPengajuanRequest extends FormRequest
{
    public const DAFTAR_URUTKAN = ['judul', 'created_at'];

    public const DAFTAR_ARAH = ['asc', 'desc'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(StatusPengajuan::class)],
            'urutkan' => ['nullable', 'string', Rule::in(self::DAFTAR_URUTKAN)],
            'arah' => ['nullable', 'string', Rule::in(self::DAFTAR_ARAH)],
        ];
    }
}
