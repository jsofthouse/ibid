<?php

namespace Tests\Feature;

use App\Enums\StatusIdentitas;
use App\Models\Karya;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VerifikasiControllerTest extends TestCase
{
    use RefreshDatabase;

    private function buatKarya(string $ibidNumber, StatusIdentitas $status): Karya
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji '.uniqid()]);
        $karya = Karya::create(['judul' => 'Judul Rahasia '.uniqid(), 'kategori_id' => $kategori->id]);
        $karya->forceFill(['ibid_number' => $ibidNumber, 'status_identitas' => $status])->save();

        return $karya->fresh();
    }

    public function test_tanpa_nomor_hanya_menampilkan_form(): void
    {
        $response = $this->get('/verifikasi');

        $response->assertOk();
        $response->assertSee('Verifikasi IBID');
        $response->assertDontSee('tidak ditemukan');
        $response->assertDontSee('valid dan terdaftar');
    }

    public function test_nomor_dipublikasikan_valid_dengan_link_ke_halaman_identitas(): void
    {
        $this->buatKarya('IRF-2026-000001', StatusIdentitas::Dipublikasikan);

        $response = $this->get('/verifikasi?nomor=IRF-2026-000001');

        $response->assertOk();
        $response->assertSee('valid dan terdaftar');
        $response->assertSee(route('buku.show', 'IRF-2026-000001'), escape: false);
    }

    public function test_nomor_ibid_diterbitkan_menampilkan_dalam_proses_penerbitan(): void
    {
        $this->buatKarya('IRF-2026-000002', StatusIdentitas::IbidDiterbitkan);

        $response = $this->get('/verifikasi?nomor=IRF-2026-000002');

        $response->assertOk();
        $response->assertSee('Dalam Proses Penerbitan');
        $response->assertDontSee(route('buku.show', 'IRF-2026-000002'), escape: false);
    }

    public function test_nomor_tidak_aktif_menampilkan_tidak_ditemukan(): void
    {
        $this->buatKarya('IRF-2026-000003', StatusIdentitas::TidakAktif);

        $this->get('/verifikasi?nomor=IRF-2026-000003')
            ->assertOk()
            ->assertSee('tidak ditemukan atau tidak berlaku');
    }

    public function test_nomor_diarsipkan_menampilkan_tidak_ditemukan(): void
    {
        $this->buatKarya('IRF-2026-000004', StatusIdentitas::Diarsipkan);

        $this->get('/verifikasi?nomor=IRF-2026-000004')
            ->assertOk()
            ->assertSee('tidak ditemukan atau tidak berlaku');
    }

    public function test_nomor_yang_tidak_ada_menampilkan_tidak_ditemukan(): void
    {
        $this->get('/verifikasi?nomor=IRF-2026-999999')
            ->assertOk()
            ->assertSee('tidak ditemukan atau tidak berlaku');
    }

    public function test_nomor_huruf_kecil_tetap_cocok(): void
    {
        $this->buatKarya('IRF-2026-000005', StatusIdentitas::Dipublikasikan);

        $this->get('/verifikasi?nomor=irf-2026-000005')
            ->assertOk()
            ->assertSee('valid dan terdaftar');
    }

    public function test_data_pribadi_dan_judul_tidak_pernah_tampil(): void
    {
        $karya = $this->buatKarya('IRF-2026-000006', StatusIdentitas::Dipublikasikan);

        $this->get('/verifikasi?nomor=IRF-2026-000006')
            ->assertOk()
            ->assertDontSee($karya->judul);
    }

    public function test_nomor_terlalu_panjang_ditolak_validasi(): void
    {
        $nomorPanjang = str_repeat('A', 60);

        $this->get('/verifikasi?nomor='.$nomorPanjang)
            ->assertSessionHasErrors('nomor');
    }

    public function test_route_verifikasi_dibatasi_throttle(): void
    {
        $route = Route::getRoutes()->getByName('verifikasi');

        $this->assertNotNull($route);
        $this->assertContains('throttle:60,1', $route->middleware());
    }
}
