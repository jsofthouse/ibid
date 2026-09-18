<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('kategori')]
#[Fillable(['nama'])]
class Kategori extends Model
{
    public function daftarKarya(): HasMany
    {
        return $this->hasMany(Karya::class);
    }

    public function daftarPengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class);
    }
}
