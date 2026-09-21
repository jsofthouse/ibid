<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function catat(
        string $aksi,
        ?string $entitas = null,
        ?int $entitasId = null,
        ?array $dataBefore = null,
        ?array $dataAfter = null,
        ?string $keterangan = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'entitas' => $entitas,
            'entitas_id' => $entitasId,
            'data_before' => $dataBefore,
            'data_after' => $dataAfter,
            'keterangan' => $keterangan,
            'ip_address' => Request::ip(),
        ]);
    }

    public function catatModel(
        string $aksi,
        Model $model,
        ?array $dataBefore = null,
        ?array $dataAfter = null,
        ?string $keterangan = null,
    ): AuditLog {
        return $this->catat(
            aksi: $aksi,
            entitas: $model->getTable(),
            entitasId: (int) $model->getKey(),
            dataBefore: $dataBefore,
            dataAfter: $dataAfter,
            keterangan: $keterangan,
        );
    }
}
