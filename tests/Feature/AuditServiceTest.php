<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Kategori;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_catat_menyimpan_aksi_lengkap_dengan_actor_dan_ip(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $log = app(AuditService::class)->catat(
            aksi: 'uji',
            entitas: 'karya',
            entitasId: 42,
            dataBefore: ['judul' => 'Lama'],
            dataAfter: ['judul' => 'Baru'],
            keterangan: 'alasan uji',
        );

        $this->assertDatabaseHas('audit_log', [
            'id' => $log->id,
            'user_id' => $user->id,
            'aksi' => 'uji',
            'entitas' => 'karya',
            'entitas_id' => 42,
            'keterangan' => 'alasan uji',
        ]);
        $this->assertSame(['judul' => 'Lama'], $log->fresh()->data_before);
        $this->assertSame(['judul' => 'Baru'], $log->fresh()->data_after);
    }

    public function test_catat_model_menurunkan_entitas_dari_nama_tabel(): void
    {
        $kategori = Kategori::create(['nama' => 'Kategori Layanan Langsung']);
        AuditLog::query()->delete();

        $log = app(AuditService::class)->catatModel('ubah', $kategori, ['nama' => 'Lama'], ['nama' => 'Baru']);

        $this->assertSame('kategori', $log->entitas);
        $this->assertSame($kategori->id, $log->entitas_id);
    }
}
