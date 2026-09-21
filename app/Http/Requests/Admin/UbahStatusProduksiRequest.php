<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusProduksi;
use App\Models\Karya;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UbahStatusProduksiRequest extends FormRequest
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
            'status_produksi' => ['required', Rule::enum(StatusProduksi::class)],
            'alasan' => [
                Rule::requiredIf(function () use ($karya) {
                    $ke = StatusProduksi::tryFrom((string) $this->input('status_produksi'));

                    return $ke && $karya->status_produksi->wajibAlasan($ke);
                }),
                'nullable', 'string', 'max:1000',
            ],
        ];
    }
}
