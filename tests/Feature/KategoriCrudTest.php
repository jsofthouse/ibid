<?php

namespace Tests\Feature;

use App\Models\Karya;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_tidak_bisa_akses_kategori(): void
    {
        $this->get('/admin/kategori')->assertRedirect(route('admin.login'));
    }

    public function test_superadmin_bisa_menambah_kategori(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post('/admin/kategori', ['nama' => 'Novel Remaja']);

        $response->assertRedirect(route('admin.kategori.index'));
        $this->assertDatabaseHas('kategori', ['nama' => 'Novel Remaja']);
    }

    public function test_nama_kategori_wajib_dan_unik(): void
    {
        $this->actingAs(User::factory()->create());
        Kategori::create(['nama' => 'Novel']);

        $this->post('/admin/kategori', ['nama' => ''])->assertSessionHasErrors('nama');
        $this->post('/admin/kategori', ['nama' => 'Novel'])->assertSessionHasErrors('nama');
    }

    public function test_superadmin_bisa_mengubah_kategori(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Nama Lama']);

        $response = $this->put("/admin/kategori/{$kategori->id}", ['nama' => 'Nama Baru']);

        $response->assertRedirect(route('admin.kategori.index'));
        $this->assertDatabaseHas('kategori', ['id' => $kategori->id, 'nama' => 'Nama Baru']);
    }

    public function test_update_boleh_pakai_nama_sendiri(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Tetap Sama']);

        $response = $this->put("/admin/kategori/{$kategori->id}", ['nama' => 'Tetap Sama']);

        $response->assertRedirect(route('admin.kategori.index'));
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_kategori_tanpa_karya_bisa_dihapus(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Akan Dihapus']);

        $response = $this->delete("/admin/kategori/{$kategori->id}");

        $response->assertRedirect(route('admin.kategori.index'));
        $this->assertSoftDeleted($kategori);
    }

    public function test_kategori_yang_dipakai_karya_tidak_bisa_dihapus(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Dipakai']);
        Karya::create(['judul' => 'Karya Uji', 'kategori_id' => $kategori->id]);

        $response = $this->delete("/admin/kategori/{$kategori->id}");

        $response->assertRedirect(route('admin.kategori.index'));
        $this->assertDatabaseHas('kategori', ['id' => $kategori->id, 'deleted_at' => null]);
    }

    public function test_pencarian_dan_urutan_whitelist(): void
    {
        $this->actingAs(User::factory()->create());
        Kategori::create(['nama' => 'Biografi']);
        Kategori::create(['nama' => 'Anak & Remaja']);

        $this->get('/admin/kategori?cari=Biografi')->assertOk()->assertSee('Biografi')->assertDontSee('Remaja');
        $this->get('/admin/kategori?urutkan=nama&arah=asc')->assertOk();
        $this->get('/admin/kategori?urutkan=kolom_tidak_valid')->assertSessionHasErrors('urutkan');
    }
}
