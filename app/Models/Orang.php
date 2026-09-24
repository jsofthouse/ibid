<?php

namespace App\Models;

use App\Enums\PeranOrang;
use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

    /**
     * Role yang di-declare untuk orang ini (orang_role), bukan assignment
     * per karya (karya_orang).
     */
    public function daftarRole(): HasMany
    {
        return $this->hasMany(OrangRole::class);
    }

    /**
     * Samakan orang_role dengan daftar yang diberikan: tambah yang belum ada,
     * hapus yang tidak lagi tercantum.
     *
     * @param  array<int, PeranOrang|string>  $daftarRole
     */
    public function sinkronkanRole(array $daftarRole): void
    {
        $nilai = collect($daftarRole)
            ->map(fn ($role) => $role instanceof PeranOrang ? $role->value : $role)
            ->unique()
            ->values();

        DB::transaction(function () use ($nilai) {
            $this->daftarRole()->whereNotIn('role', $nilai)->delete();

            foreach ($nilai as $role) {
                $this->daftarRole()->firstOrCreate(['role' => $role]);
            }
        });
    }
}
