<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('kategori')]
#[Fillable(['nama'])]
class Kategori extends Model
{
    use SoftDeletes;

    public function daftarKarya(): HasMany
    {
        return $this->hasMany(Karya::class);
    }

    public function daftarPengajuan(): HasMany
    {
        return $this->hasMany(Pengajuan::class);
    }
}
