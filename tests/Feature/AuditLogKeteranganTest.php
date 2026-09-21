<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogKeteranganTest extends TestCase
{
    use RefreshDatabase;

    public function test_keterangan_bisa_diisi_dan_nullable(): void
    {
        $denganKeterangan = AuditLog::create([
            'aksi' => 'uji',
            'entitas' => 'karya',
            'entitas_id' => 1,
            'keterangan' => 'Alasan uji coba',
        ]);

        $tanpaKeterangan = AuditLog::create([
            'aksi' => 'uji',
            'entitas' => 'karya',
            'entitas_id' => 2,
        ]);

        $this->assertSame('Alasan uji coba', $denganKeterangan->fresh()->keterangan);
        $this->assertNull($tanpaKeterangan->fresh()->keterangan);
    }
}
