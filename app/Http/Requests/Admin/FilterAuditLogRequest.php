<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterAuditLogRequest extends FormRequest
{
    public const DAFTAR_AKSI = [
        'buat', 'ubah', 'hapus', 'pulihkan', 'login_berhasil', 'login_gagal',
    ];

    public const DAFTAR_ENTITAS = [
        'karya', 'kategori', 'orang', 'pengajuan', 'auth',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'aksi' => ['nullable', 'string', Rule::in(self::DAFTAR_AKSI)],
            'entitas' => ['nullable', 'string', Rule::in(self::DAFTAR_ENTITAS)],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ];
    }
}
