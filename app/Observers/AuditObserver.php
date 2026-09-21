<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    private const KOLOM_DIABAIKAN = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function __construct(private readonly AuditService $auditService) {}

    public function created(Model $model): void
    {
        $this->auditService->catatModel('buat', $model, null, $this->snapshot($model));
    }

    public function updated(Model $model): void
    {
        $before = [];
        $after = [];

        foreach ($model->getChanges() as $kolom => $nilaiBaru) {
            if (in_array($kolom, self::KOLOM_DIABAIKAN, true)) {
                continue;
            }

            $before[$kolom] = $model->getOriginal($kolom);
            $after[$kolom] = $nilaiBaru;
        }

        if ($before === []) {
            return;
        }

        $this->auditService->catatModel('ubah', $model, $before, $after);
    }

    public function deleted(Model $model): void
    {
        $aksi = method_exists($model, 'trashed') && $model->trashed() ? 'hapus' : 'hapus_permanen';

        $this->auditService->catatModel($aksi, $model, $this->snapshot($model), null);
    }

    public function restored(Model $model): void
    {
        $this->auditService->catatModel('pulihkan', $model, null, $this->snapshot($model));
    }

    private function snapshot(Model $model): array
    {
        return collect($model->getAttributes())
            ->except(self::KOLOM_DIABAIKAN)
            ->toArray();
    }
}
