<?php

namespace App\Http\Requests\Publik;

use Illuminate\Foundation\Http\FormRequest;

class VerifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor' => ['nullable', 'string', 'max:50'],
        ];
    }
}
