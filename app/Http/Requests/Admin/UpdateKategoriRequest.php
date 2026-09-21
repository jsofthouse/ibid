<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => [
                'required', 'string', 'max:255',
                Rule::unique('kategori', 'nama')->ignore($this->route('kategori')),
            ],
        ];
    }
}
