<?php

namespace App\Models;

use App\Enums\StatusPengajuan;
use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('pengajuan')]
#[ObservedBy(AuditObserver::class)]
#[Fillable([
    'nama', 'nama_pena', 'email', 'nomor_wa', 'alamat', 'kota', 'provinsi',
    'judul', 'kategori_id', 'sinopsis', 'naskah_path', 'surat_keaslian_path',
    'status', 'catatan_admin', 'karya_id',
])]
class Pengajuan extends Model
{
    use SoftDeletes;

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
