<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterKaryaRequest extends FormRequest
{
    public const DAFTAR_URUTKAN = ['judul', 'created_at', 'tahun_terbit'];

    public const DAFTAR_ARAH = ['asc', 'desc'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cari' => ['nullable', 'string', 'max:255'],
            'status_produksi' => ['nullable', Rule::enum(StatusProduksi::class)],
            'status_identitas' => ['nullable', Rule::enum(StatusIdentitas::class)],
            'urutkan' => ['nullable', 'string', Rule::in(self::DAFTAR_URUTKAN)],
            'arah' => ['nullable', 'string', Rule::in(self::DAFTAR_ARAH)],
        ];
    }
}
