<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Orang;
use App\Models\Pengajuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteScaffoldTest extends TestCase
{
    use RefreshDatabase;

    public function test_kategori_soft_delete_tidak_hilang_dari_database(): void
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji']);

        $kategori->delete();

        $this->assertSoftDeleted($kategori);
        $this->assertDatabaseHas('kategori', ['id' => $kategori->id, 'nama' => 'Kategori Uji']);
    }

    public function test_orang_soft_delete_tidak_hilang_dari_database(): void
    {
        $orang = Orang::create(['nama' => 'Orang Uji']);

        $orang->delete();

        $this->assertSoftDeleted($orang);
    }

    public function test_pengajuan_soft_delete_tidak_hilang_dari_database(): void
    {
        $kategori = Kategori::create(['nama' => 'Kategori Pengajuan']);

        $pengajuan = Pengajuan::create([
            'nama' => 'Pengaju Uji',
            'email' => 'pengaju@example.com',
            'judul' => 'Naskah Uji',
            'kategori_id' => $kategori->id,
            'naskah_path' => 'naskah/contoh.pdf',
        ]);

        $pengajuan->delete();

        $this->assertSoftDeleted($pengajuan);
    }
}
