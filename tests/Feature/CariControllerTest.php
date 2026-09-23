<?php

namespace Tests\Feature;

use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CariControllerTest extends TestCase
{
    use RefreshDatabase;

    private function buatKaryaDipublikasikan(array $atribut = []): Karya
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji '.uniqid()]);

        $karya = Karya::create(array_merge([
            'judul' => 'Judul Uji '.uniqid(),
            'kategori_id' => $kategori->id,
        ], $atribut));

        $karya->forceFill([
            'ibid_number' => 'IRF-2026-'.str_pad((string) $karya->id, 6, '0', STR_PAD_LEFT),
            'status_identitas' => StatusIdentitas::Dipublikasikan,
            'cover_path' => 'cover/contoh.jpg',
        ])->save();

        return $karya->fresh();
    }

    public function test_tanpa_kata_kunci_menampilkan_semua_karya_dipublikasikan(): void
    {
        $this->buatKaryaDipublikasikan(['judul' => 'Karya Pertama']);
        $this->buatKaryaDipublikasikan(['judul' => 'Karya Kedua']);

        $response = $this->get('/cari');

        $response->assertOk()->assertSee('Karya Pertama')->assertSee('Karya Kedua');
    }

    public function test_karya_belum_dipublikasikan_tidak_pernah_muncul(): void
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji']);

        $draft = Karya::create(['judul' => 'Karya Draft Rahasia', 'kategori_id' => $kategori->id]);

        $praTerbit = Karya::create(['judul' => 'Karya Pra-Terbit Rahasia', 'kategori_id' => $kategori->id]);
        $praTerbit->forceFill(['ibid_number' => 'IRF-2026-000010', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();

        $tidakAktif = Karya::create(['judul' => 'Karya Tidak Aktif Rahasia', 'kategori_id' => $kategori->id]);
        $tidakAktif->forceFill(['ibid_number' => 'IRF-2026-000011', 'status_identitas' => StatusIdentitas::TidakAktif])->save();

        $diarsipkan = Karya::create(['judul' => 'Karya Diarsipkan Rahasia', 'kategori_id' => $kategori->id]);
        $diarsipkan->forceFill(['ibid_number' => 'IRF-2026-000012', 'status_identitas' => StatusIdentitas::Diarsipkan])->save();

        $response = $this->get('/cari');

        $response->assertOk();
        $response->assertDontSee('Karya Draft Rahasia');
        $response->assertDontSee('Karya Pra-Terbit Rahasia');
        $response->assertDontSee('Karya Tidak Aktif Rahasia');
        $response->assertDontSee('Karya Diarsipkan Rahasia');
    }

    public function test_pencarian_tidak_pernah_menampilkan_karya_non_dipublikasikan_meski_judul_cocok(): void
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji']);
        $draft = Karya::create(['judul' => 'Novel Sangat Unik Sekali', 'kategori_id' => $kategori->id]);

        $response = $this->get('/cari?q=Sangat+Unik');

        $response->assertOk()->assertDontSee('Novel Sangat Unik Sekali');
    }

    public function test_cari_berdasarkan_judul(): void
    {
        $this->buatKaryaDipublikasikan(['judul' => 'Menyusuri Jejak Senja']);
        $this->buatKaryaDipublikasikan(['judul' => 'Kisah Lain']);

        $response = $this->get('/cari?q=Jejak+Senja');

        $response->assertOk()->assertSee('Menyusuri Jejak Senja')->assertDontSee('Kisah Lain');
    }

    public function test_cari_berdasarkan_nomor_ibid(): void
    {
        $karya = $this->buatKaryaDipublikasikan(['judul' => 'Karya Bernomor']);
        $lain = $this->buatKaryaDipublikasikan(['judul' => 'Karya Lain']);

        $response = $this->get('/cari?q='.$karya->ibid_number);

        $response->assertOk()->assertSee('Karya Bernomor')->assertDontSee('Karya Lain');
    }

    public function test_cari_berdasarkan_isbn(): void
    {
        $this->buatKaryaDipublikasikan(['judul' => 'Karya Ber-ISBN', 'isbn' => '978-000-111-222']);
        $this->buatKaryaDipublikasikan(['judul' => 'Karya Tanpa ISBN Match']);

        $response = $this->get('/cari?q=978-000-111-222');

        $response->assertOk()->assertSee('Karya Ber-ISBN')->assertDontSee('Karya Tanpa ISBN Match');
    }

    public function test_cari_berdasarkan_nama_penulis_dan_nama_pena(): void
    {
        $karyaA = $this->buatKaryaDipublikasikan(['judul' => 'Karya Penulis A']);
        $penulis = Orang::create(['nama' => 'Nama Asli Sangat Unik', 'nama_pena' => 'Pena Sangat Unik']);
        $karyaA->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);

        $karyaB = $this->buatKaryaDipublikasikan(['judul' => 'Karya Editor Saja']);
        $editor = Orang::create(['nama' => 'Editor Sangat Unik']);
        $karyaB->daftarOrang()->attach($editor->id, ['role' => PeranOrang::Editor->value]);

        $responseNamaAsli = $this->get('/cari?q=Nama+Asli+Sangat+Unik');
        $responseNamaAsli->assertOk()->assertSee('Karya Penulis A')->assertDontSee('Karya Editor Saja');

        $responseNamaPena = $this->get('/cari?q=Pena+Sangat+Unik');
        $responseNamaPena->assertOk()->assertSee('Karya Penulis A');

        $responseEditor = $this->get('/cari?q=Editor+Sangat+Unik');
        $responseEditor->assertOk()->assertDontSee('Karya Editor Saja');
    }

    public function test_data_pribadi_penulis_tidak_pernah_tampil_di_hasil_pencarian(): void
    {
        $karya = $this->buatKaryaDipublikasikan(['judul' => 'Karya Dengan Penulis']);
        $penulis = Orang::create([
            'nama' => 'Penulis Uji',
            'email' => 'penulis-rahasia@example.com',
            'nomor_wa' => '081234567890',
            'alamat' => 'Jl. Rahasia No. 1',
        ]);
        $karya->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);

        $response = $this->get('/cari');

        $response->assertDontSee('penulis-rahasia@example.com');
        $response->assertDontSee('081234567890');
        $response->assertDontSee('Jl. Rahasia No. 1');
    }

    public function test_pencarian_tanpa_hasil_menampilkan_pesan_kosong(): void
    {
        $this->buatKaryaDipublikasikan(['judul' => 'Karya Yang Ada']);

        $response = $this->get('/cari?q=KataKunciYangTidakAdaSamaSekali');

        $response->assertOk()->assertSee('Tidak ada karya yang cocok');
    }

    public function test_whitelist_urutkan_menolak_kolom_tidak_dikenal(): void
    {
        $this->get('/cari?urutkan=cover_path')->assertSessionHasErrors('urutkan');
    }

    public function test_whitelist_arah_menolak_nilai_tidak_dikenal(): void
    {
        $this->get('/cari?arah=menyamping')->assertSessionHasErrors('arah');
    }

    public function test_route_cari_dibatasi_throttle(): void
    {
        $route = Route::getRoutes()->getByName('cari');

        $this->assertNotNull($route);
        $this->assertContains('throttle:60,1', $route->middleware());
    }
}
