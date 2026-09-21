<?php

namespace Tests\Feature;

use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrangCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_tidak_bisa_akses_orang(): void
    {
        $this->get('/admin/orang')->assertRedirect(route('admin.login'));
    }

    public function test_superadmin_bisa_menambah_orang(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post('/admin/orang', [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'nomor_wa' => '0812-3456-7890',
        ]);

        $response->assertRedirect(route('admin.orang.index'));
        $this->assertDatabaseHas('orang', ['nama' => 'Budi Santoso', 'email' => 'budi@example.com']);
    }

    public function test_nama_wajib_email_harus_valid(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/admin/orang', ['nama' => ''])->assertSessionHasErrors('nama');
        $this->post('/admin/orang', ['nama' => 'Budi', 'email' => 'bukan-email'])->assertSessionHasErrors('email');
    }

    public function test_superadmin_bisa_mengubah_orang(): void
    {
        $this->actingAs(User::factory()->create());
        $orang = Orang::create(['nama' => 'Nama Lama']);

        $response = $this->put("/admin/orang/{$orang->id}", ['nama' => 'Nama Baru']);

        $response->assertRedirect(route('admin.orang.index'));
        $this->assertDatabaseHas('orang', ['id' => $orang->id, 'nama' => 'Nama Baru']);
    }

    public function test_orang_tanpa_karya_bisa_dihapus(): void
    {
        $this->actingAs(User::factory()->create());
        $orang = Orang::create(['nama' => 'Akan Dihapus']);

        $response = $this->delete("/admin/orang/{$orang->id}");

        $response->assertRedirect(route('admin.orang.index'));
        $this->assertSoftDeleted($orang);
    }

    public function test_orang_yang_dipakai_karya_tidak_bisa_dihapus(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);
        $karya = Karya::create(['judul' => 'Karya Uji', 'kategori_id' => $kategori->id]);
        $orang = Orang::create(['nama' => 'Penulis Uji']);
        $orang->daftarKarya()->attach($karya->id, ['role' => 'penulis']);

        $response = $this->delete("/admin/orang/{$orang->id}");

        $response->assertRedirect(route('admin.orang.index'));
        $this->assertDatabaseHas('orang', ['id' => $orang->id, 'deleted_at' => null]);
    }
}
