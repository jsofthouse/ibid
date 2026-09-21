<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusIdentitas;
use App\Models\Karya;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatalkanPenerbitanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Karya $karya */
        $karya = $this->route('karya');
        $sudahBerIbid = $karya->status_identitas !== StatusIdentitas::BelumBerIbid;

        return [
            'alasan' => ['required', 'string', 'max:1000'],
            'konfirmasi_nomor_ibid' => [Rule::requiredIf($sudahBerIbid), 'nullable', 'string'],
        ];
    }
}
