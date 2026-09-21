<?php

namespace App\Models;

use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('orang')]
#[ObservedBy(AuditObserver::class)]
#[Fillable(['nama', 'nama_pena', 'email', 'nomor_wa', 'alamat', 'kota', 'provinsi'])]
class Orang extends Model
{
    use SoftDeletes;

    public function daftarKarya(): BelongsToMany
    {
        return $this->belongsToMany(Karya::class, 'karya_orang', 'orang_id', 'karya_id')
            ->using(KaryaOrang::class)
            ->withPivot('role')
            ->withTimestamps();
    }
}
