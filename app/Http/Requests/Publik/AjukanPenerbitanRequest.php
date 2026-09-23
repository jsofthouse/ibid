<?php

namespace App\Http\Requests\Publik;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AjukanPenerbitanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nama_pena' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'nomor_wa' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'kota' => ['nullable', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'judul' => ['required', 'string', 'max:500'],
            'kategori_id' => ['required', Rule::exists('kategori', 'id')->whereNull('deleted_at')],
            'sinopsis' => ['nullable', 'string', 'max:5000'],
            // Format PDF/DOC/DOCX asli divalidasi lewat signature isi berkas
            // di NaskahUploadService, bukan rule mimes: di sini - rule mimes:
            // Laravel murni mengandalkan guessExtension()/finfo, yang
            // terverifikasi tidak bisa membedakan .docx modern dari ZIP
            // biasa (lihat NaskahUploadService::cekDocx()) dan berisiko
            // menolak .docx asli yang sah.
            'naskah' => ['required', 'file', 'max:10240'],
            'surat_keaslian' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
