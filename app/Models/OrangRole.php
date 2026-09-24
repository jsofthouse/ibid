<?php

namespace App\Models;

use App\Enums\PeranOrang;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('orang_role', timestamps: false)]
#[Fillable(['orang_id', 'role'])]
class OrangRole extends Model
{
    protected function casts(): array
    {
        return [
            'role' => PeranOrang::class,
        ];
    }

    public function orang(): BelongsTo
    {
        return $this->belongsTo(Orang::class);
    }
}
