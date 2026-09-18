<?php

namespace App\Models;

use App\Enums\PeranOrang;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Table('karya_orang')]
#[Fillable(['karya_id', 'orang_id', 'role'])]
class KaryaOrang extends Pivot
{
    protected function casts(): array
    {
        return [
            'role' => PeranOrang::class,
        ];
    }

    public function karya(): BelongsTo
    {
        return $this->belongsTo(Karya::class);
    }

    public function orang(): BelongsTo
    {
        return $this->belongsTo(Orang::class);
    }
}
