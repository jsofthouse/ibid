<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuat_kategori_tercatat_di_audit_log(): void
    {
        $kategori = Kategori::create(['nama' => 'Novel Remaja']);

        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'buat',
            'entitas' => 'kategori',
            'entitas_id' => $kategori->id,
        ]);
    }

    public function test_mengubah_kategori_mencatat_nilai_sebelum_dan_sesudah(): void
    {
        $kategori = Kategori::create(['nama' => 'Nama Lama']);

        $kategori->update(['nama' => 'Nama Baru']);

        $log = AuditLog::query()->where('aksi', 'ubah')->where('entitas_id', $kategori->id)->firstOrFail();

        $this->assertSame(['nama' => 'Nama Lama'], $log->data_before);
        $this->assertSame(['nama' => 'Nama Baru'], $log->data_after);
    }

    public function test_soft_delete_kategori_tercatat_sebagai_hapus(): void
    {
        $kategori = Kategori::create(['nama' => 'Akan Dihapus']);

        $kategori->delete();

        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'hapus',
            'entitas' => 'kategori',
            'entitas_id' => $kategori->id,
        ]);
    }

    public function test_restore_kategori_tercatat_sebagai_pulihkan(): void
    {
        $kategori = Kategori::create(['nama' => 'Dipulihkan']);
        $kategori->delete();

        $kategori->restore();

        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'pulihkan',
            'entitas' => 'kategori',
            'entitas_id' => $kategori->id,
        ]);
    }
}
