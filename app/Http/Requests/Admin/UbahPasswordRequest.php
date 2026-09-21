<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UbahPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_saat_ini' => ['required', 'string', 'current_password:web'],
            'password_baru' => [
                'required', 'string', 'confirmed', 'different:password_saat_ini',
                Password::min(8)->letters()->numbers(),
            ],
        ];
    }
}
