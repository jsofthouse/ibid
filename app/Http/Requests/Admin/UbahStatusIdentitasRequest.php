<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusIdentitas;
use App\Models\Karya;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UbahStatusIdentitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Karya $karya */
        $karya = $this->route('karya');

        return [
            'status_identitas' => ['required', Rule::enum(StatusIdentitas::class)],
            'alasan' => [
                Rule::requiredIf(function () use ($karya) {
                    $ke = StatusIdentitas::tryFrom((string) $this->input('status_identitas'));

                    return $ke && $karya->status_identitas->wajibAlasan($ke);
                }),
                'nullable', 'string', 'max:1000',
            ],
        ];
    }
}
