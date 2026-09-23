<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusPengajuan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UbahStatusPengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(StatusPengajuan::class)],
            'catatan_admin' => [
                Rule::requiredIf(fn () => StatusPengajuan::tryFrom((string) $this->input('status')) === StatusPengajuan::Ditolak),
                'nullable', 'string', 'max:1000',
            ],
        ];
    }
}
