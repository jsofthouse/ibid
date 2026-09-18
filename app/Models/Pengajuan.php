<?php

namespace App\Models;

use App\Enums\StatusPengajuan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('pengajuan')]
#[Fillable([
    'nama', 'nama_pena', 'email', 'nomor_wa', 'alamat', 'kota', 'provinsi',
    'judul', 'kategori_id', 'sinopsis', 'naskah_path', 'surat_keaslian_path',
    'status', 'catatan_admin', 'karya_id',
])]
class Pengajuan extends Model
{
    protected function casts(): array
    {
        return [
            'status' => StatusPengajuan::class,
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function karya(): BelongsTo
    {
        return $this->belongsTo(Karya::class);
    }
}
