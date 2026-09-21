<?php

namespace App\Http\Requests\Admin;

use App\Enums\PeranOrang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKaryaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'judul' => ['required', 'string', 'max:500'],
            'subjudul' => ['nullable', 'string', 'max:500'],
            'kategori_id' => ['required', Rule::exists('kategori', 'id')->whereNull('deleted_at')],
            'isbn' => ['nullable', 'string', 'max:50'],
            'tahun_terbit' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'kota_terbit' => ['nullable', 'string', 'max:255'],
            'edisi' => ['nullable', 'string', 'max:100'],
            'bahasa' => ['nullable', 'string', 'max:100'],
            'jumlah_halaman' => ['nullable', 'integer', 'min:1'],
            'ukuran' => ['nullable', 'string', 'max:100'],
            'sinopsis' => ['nullable', 'string', 'max:5000'],
            'kata_kunci' => ['nullable', 'string', 'max:500'],
            'tampil_pra_terbit' => ['nullable', 'boolean'],
            'tanggal_dibuat' => ['nullable', 'date'],
            'tanggal_diterbitkan' => ['nullable', 'date'],
            'cover' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ];

        foreach (PeranOrang::cases() as $peran) {
            $rules[$peran->value] = ['nullable', 'array'];
            $rules[$peran->value.'.*'] = [Rule::exists('orang', 'id')->whereNull('deleted_at')];
        }

        return $rules;
    }
}
