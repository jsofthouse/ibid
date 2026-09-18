<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Table('orang')]
#[Fillable(['nama', 'nama_pena', 'email', 'nomor_wa', 'alamat', 'kota', 'provinsi'])]
class Orang extends Model
{
    public function daftarKarya(): BelongsToMany
    {
        return $this->belongsToMany(Karya::class, 'karya_orang', 'orang_id', 'karya_id')
            ->using(KaryaOrang::class)
            ->withPivot('role')
            ->withTimestamps();
    }
}
