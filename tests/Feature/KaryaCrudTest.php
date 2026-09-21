<?php

namespace Tests\Feature;

use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KaryaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('storage-karya');
    }

    public function test_guest_tidak_bisa_akses_karya(): void
    {
        $this->get('/admin/karya')->assertRedirect(route('admin.login'));
    }

    public function test_superadmin_bisa_menambah_karya_dengan_penulis_dan_editor(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);
        $penulis = Orang::create(['nama' => 'Penulis A']);
        $editor = Orang::create(['nama' => 'Editor B']);

        $response = $this->post('/admin/karya', [
            'judul' => 'Karya Pertama',
            'kategori_id' => $kategori->id,
            'penulis' => [$penulis->id],
            'editor' => [$editor->id],
        ]);

        $karya = Karya::firstWhere('judul', 'Karya Pertama');
        $response->assertRedirect(route('admin.karya.show', $karya));

        $this->assertDatabaseHas('karya_orang', ['karya_id' => $karya->id, 'orang_id' => $penulis->id, 'role' => 'penulis']);
        $this->assertDatabaseHas('karya_orang', ['karya_id' => $karya->id, 'orang_id' => $editor->id, 'role' => 'editor']);
    }

    public function test_satu_orang_bisa_punya_lebih_dari_satu_peran(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);
        $orang = Orang::create(['nama' => 'Multi Peran']);

        $this->post('/admin/karya', [
            'judul' => 'Karya Multi Peran',
            'kategori_id' => $kategori->id,
            'penulis' => [$orang->id],
            'penerjemah' => [$orang->id],
        ]);

        $karya = Karya::firstWhere('judul', 'Karya Multi Peran');

        $this->assertDatabaseHas('karya_orang', ['karya_id' => $karya->id, 'orang_id' => $orang->id, 'role' => 'penulis']);
        $this->assertDatabaseHas('karya_orang', ['karya_id' => $karya->id, 'orang_id' => $orang->id, 'role' => 'penerjemah']);
    }

    public function test_judul_dan_kategori_wajib(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/admin/karya', ['judul' => ''])->assertSessionHasErrors(['judul', 'kategori_id']);
    }

    public function test_upload_cover_valid_tersimpan(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);

        $response = $this->post('/admin/karya', [
            'judul' => 'Karya Cover',
            'kategori_id' => $kategori->id,
            'cover' => UploadedFile::fake()->image('cover.jpg', 800, 1200),
        ]);

        $karya = Karya::firstWhere('judul', 'Karya Cover');
        $response->assertRedirect(route('admin.karya.show', $karya));
        $this->assertNotNull($karya->cover_path);
        Storage::disk('storage-karya')->assertExists($karya->cover_path);
    }

    public function test_cover_tidak_valid_ditolak_dengan_error(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);

        $response = $this->post('/admin/karya', [
            'judul' => 'Karya Cover Rusak',
            'kategori_id' => $kategori->id,
            'cover' => UploadedFile::fake()->create('cover.jpg', 20000),
        ]);

        $response->assertSessionHasErrors('cover');
        $this->assertNull(Karya::firstWhere('judul', 'Karya Cover Rusak'));
    }

    public function test_ganti_cover_menghapus_cover_lama(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);
        $karya = Karya::create(['judul' => 'Karya Ganti Cover', 'kategori_id' => $kategori->id]);

        $this->put("/admin/karya/{$karya->id}", [
            'judul' => 'Karya Ganti Cover',
            'kategori_id' => $kategori->id,
            'cover' => UploadedFile::fake()->image('lama.jpg', 800, 1200),
        ]);
        $coverLama = $karya->fresh()->cover_path;
        Storage::disk('storage-karya')->assertExists($coverLama);

        $this->put("/admin/karya/{$karya->id}", [
            'judul' => 'Karya Ganti Cover',
            'kategori_id' => $kategori->id,
            'cover' => UploadedFile::fake()->image('baru.jpg', 800, 1200),
        ]);

        Storage::disk('storage-karya')->assertMissing($coverLama);
        $this->assertNotSame($coverLama, $karya->fresh()->cover_path);
    }

    public function test_karya_belum_ber_ibid_bisa_dihapus(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);
        $karya = Karya::create(['judul' => 'Karya Hapus', 'kategori_id' => $kategori->id]);

        $response = $this->delete("/admin/karya/{$karya->id}");

        $response->assertRedirect(route('admin.karya.index'));
        $this->assertSoftDeleted($karya);
    }

    public function test_karya_yang_sudah_ber_ibid_tidak_bisa_dihapus(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);
        $karya = Karya::create(['judul' => 'Karya Ber-IBID', 'kategori_id' => $kategori->id]);
        $karya->forceFill(['ibid_number' => 'IRF-2026-000001', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();

        $response = $this->delete("/admin/karya/{$karya->id}");

        $response->assertRedirect(route('admin.karya.index'));
        $this->assertDatabaseHas('karya', ['id' => $karya->id, 'deleted_at' => null]);
    }

    public function test_filter_dua_sumbu_status_dan_pencarian(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);
        $draft = Karya::create(['judul' => 'Karya Draft Saja', 'kategori_id' => $kategori->id]);
        $lain = Karya::create(['judul' => 'Karya Lain', 'kategori_id' => $kategori->id]);
        $lain->forceFill(['status_produksi' => StatusProduksi::Disetujui])->save();

        $response = $this->get('/admin/karya?status_produksi=draft&cari=Draft');

        $response->assertOk()->assertSee('Karya Draft Saja')->assertDontSee('Karya Lain');
    }

    public function test_whitelist_urutkan_menolak_kolom_tidak_dikenal(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/karya?urutkan=cover_path')->assertSessionHasErrors('urutkan');
    }

    public function test_halaman_detail_menampilkan_riwayat(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);
        $karya = Karya::create(['judul' => 'Karya Riwayat', 'kategori_id' => $kategori->id]);

        $response = $this->get(route('admin.karya.show', $karya));

        $response->assertOk()->assertSee('Riwayat')->assertSee('Buat');
    }
}
