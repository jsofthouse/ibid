<?php

namespace App\Models;

use App\Enums\StatusKarya;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('karya')]
#[Fillable([
    'ibid_number', 'isbn', 'judul', 'subjudul', 'kategori_id',
    'tahun_terbit', 'kota_terbit', 'edisi', 'bahasa', 'jumlah_halaman',
    'ukuran', 'sinopsis', 'kata_kunci', 'cover_path', 'status',
    'tanggal_dibuat', 'tanggal_diterbitkan',
])]
class Karya extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => StatusKarya::class,
            'tanggal_dibuat' => 'date',
            'tanggal_diterbitkan' => 'date',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function daftarOrang(): BelongsToMany
    {
        return $this->belongsToMany(Orang::class, 'karya_orang', 'karya_id', 'orang_id')
            ->using(KaryaOrang::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function pengajuan(): HasOne
    {
        return $this->hasOne(Pengajuan::class);
    }
}
