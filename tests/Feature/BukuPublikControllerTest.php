<?php

namespace Tests\Feature;

use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BukuPublikControllerTest extends TestCase
{
    use RefreshDatabase;

    private function buatKaryaBerIbid(array $atribut = []): Karya
    {
        $kategori = Kategori::create(['nama' => 'Novel']);
        $karya = Karya::create(array_merge([
            'judul' => 'Jejak Senja',
            'subjudul' => 'Kisah di Ujung Hari',
            'sinopsis' => 'Sinopsis rahasia yang cukup panjang untuk diuji.',
        ], $atribut, ['kategori_id' => $kategori->id]));

        $penulis = Orang::create([
            'nama' => 'Nama Asli Penulis',
            'nama_pena' => 'Pena Rahasia',
            'email' => 'penulis-rahasia@example.com',
            'nomor_wa' => '081200000000',
            'alamat' => 'Jl. Rahasia No. 1',
        ]);
        $karya->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);

        $karya->forceFill([
            'ibid_number' => 'IRF-2026-000001',
            'status_identitas' => StatusIdentitas::IbidDiterbitkan,
            'tanggal_ibid' => now()->toDateString(),
        ])->save();

        return $karya->fresh();
    }

    public function test_ibid_tidak_dikenal_menghasilkan_404(): void
    {
        $this->get('/buku/IRF-2026-999999')->assertNotFound();
    }

    public function test_halaman_pra_terbit_menampilkan_field_whitelist_saja(): void
    {
        $karya = $this->buatKaryaBerIbid();

        $response = $this->get('/buku/'.$karya->ibid_number);

        $response->assertOk();
        $response->assertSee('Jejak Senja');
        $response->assertSee('Dalam Proses Penerbitan');
        $response->assertSee('Pena Rahasia');
        $response->assertSee($karya->ibid_number);

        // Whitelist: data pribadi & data bibliografis belum final tidak boleh tampil
        $response->assertDontSee('penulis-rahasia@example.com');
        $response->assertDontSee('081200000000');
        $response->assertDontSee('Jl. Rahasia No. 1');
        $response->assertDontSee('Nama Asli Penulis');

        $response->assertSee('noindex', false);
    }

    public function test_halaman_pra_terbit_tidak_menampilkan_data_bibliografis_belum_final(): void
    {
        $karya = $this->buatKaryaBerIbid([
            'tahun_terbit' => 2099,
            'kota_terbit' => 'KotaRahasiaUnik',
            'isbn' => 'ISBN-RAHASIA-123',
        ]);

        $response = $this->get('/buku/'.$karya->ibid_number);

        $response->assertDontSee('2099');
        $response->assertDontSee('KotaRahasiaUnik');
        $response->assertDontSee('ISBN-RAHASIA-123');
    }

    public function test_halaman_pra_terbit_dengan_tampil_pra_terbit_nonaktif_hanya_ibid_dan_badge(): void
    {
        $karya = $this->buatKaryaBerIbid(['tampil_pra_terbit' => false]);

        $response = $this->get('/buku/'.$karya->ibid_number);

        $response->assertOk();
        $response->assertSee($karya->ibid_number);
        $response->assertSee('Dalam Proses Penerbitan');
        $response->assertSee('noindex', false);

        $response->assertDontSee('Jejak Senja');
        $response->assertDontSee('Kisah di Ujung Hari');
        $response->assertDontSee('Pena Rahasia');
        $response->assertDontSee('Sinopsis rahasia yang cukup panjang untuk diuji.');
        $response->assertDontSee('og:title', false);
        $response->assertDontSee('og:image', false);
    }

    public function test_halaman_pra_terbit_dengan_tampil_pra_terbit_aktif_tetap_lengkap(): void
    {
        $karya = $this->buatKaryaBerIbid(['tampil_pra_terbit' => true]);

        $response = $this->get('/buku/'.$karya->ibid_number);

        $response->assertOk();
        $response->assertSee('Jejak Senja');
        $response->assertSee('Pena Rahasia');
        $response->assertSee('og:title', false);
    }

    public function test_halaman_dipublikasikan_menampilkan_data_lengkap(): void
    {
        $karya = $this->buatKaryaBerIbid([
            'tahun_terbit' => 2026,
            'kota_terbit' => 'Jakarta',
            'edisi' => '1',
            'bahasa' => 'Indonesia',
            'jumlah_halaman' => 200,
            'ukuran' => '14x21 cm',
            'isbn' => '978-000-000',
            'cover_path' => 'cover/contoh.jpg',
        ]);
        $karya->forceFill(['status_produksi' => StatusProduksi::Diterbitkan])->save();
        $karya->forceFill([
            'status_identitas' => StatusIdentitas::Dipublikasikan,
            'tanggal_dipublikasikan' => now()->toDateString(),
        ])->save();

        $response = $this->get('/buku/'.$karya->ibid_number);

        $response->assertOk();
        $response->assertSee('Jejak Senja');
        $response->assertSee('Jakarta');
        $response->assertSee('978-000-000');
        $response->assertSee('200');
        $response->assertDontSeeText('noindex');
    }

    public function test_halaman_tidak_aktif_tanpa_pernah_dipublikasikan_tidak_tampilkan_judul(): void
    {
        $karya = $this->buatKaryaBerIbid();
        $karya->forceFill(['status_identitas' => StatusIdentitas::TidakAktif])->save();

        $response = $this->get('/buku/'.$karya->ibid_number);

        $response->assertOk();
        $response->assertDontSee('Jejak Senja');
        $response->assertSee('dibatalkan / tidak berlaku');
        $response->assertSee($karya->ibid_number);
    }

    public function test_halaman_tidak_aktif_yang_pernah_dipublikasikan_tetap_tampilkan_judul(): void
    {
        $karya = $this->buatKaryaBerIbid();
        $karya->forceFill([
            'status_identitas' => StatusIdentitas::TidakAktif,
            'tanggal_dipublikasikan' => now()->subDay()->toDateString(),
        ])->save();

        $response = $this->get('/buku/'.$karya->ibid_number);

        $response->assertOk();
        $response->assertSee('Jejak Senja');
        $response->assertSee('dibatalkan / tidak berlaku');
    }

    public function test_halaman_diarsipkan_perilaku_sama_dengan_tidak_aktif(): void
    {
        $karya = $this->buatKaryaBerIbid();
        $karya->forceFill(['status_identitas' => StatusIdentitas::Diarsipkan])->save();

        $response = $this->get('/buku/'.$karya->ibid_number);

        $response->assertOk();
        $response->assertDontSee('Jejak Senja');
        $response->assertSee('dibatalkan / tidak berlaku');
    }

    public function test_route_buku_dibatasi_throttle(): void
    {
        $route = Route::getRoutes()->getByName('buku.show');

        $this->assertNotNull($route);
        $this->assertContains('throttle:60,1', $route->middleware());
    }
}
