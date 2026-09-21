<?php

namespace Tests\Feature;

use App\Enums\PeranOrang;
use App\Enums\StatusProduksi;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryaIbidControllerTest extends TestCase
{
    use RefreshDatabase;

    private function buatKaryaSiapIbid(): Karya
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji']);
        $penulis = Orang::create(['nama' => 'Penulis Uji']);
        $karya = Karya::create(['judul' => 'Karya Uji', 'kategori_id' => $kategori->id]);
        $karya->forceFill(['status_produksi' => StatusProduksi::Disetujui])->save();
        $karya->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);

        return $karya->fresh();
    }

    public function test_guest_tidak_bisa_generate_ibid(): void
    {
        $karya = $this->buatKaryaSiapIbid();

        $this->post(route('admin.karya.generate-ibid', $karya))->assertRedirect(route('admin.login'));
    }

    public function test_superadmin_bisa_generate_ibid_lewat_http(): void
    {
        $this->actingAs(User::factory()->create());
        $karya = $this->buatKaryaSiapIbid();

        $response = $this->post(route('admin.karya.generate-ibid', $karya));

        $response->assertRedirect(route('admin.karya.show', $karya));
        $response->assertSessionHas('sukses');
        $this->assertNotNull($karya->fresh()->ibid_number);
    }

    public function test_generate_gagal_diflash_sebagai_gagal(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Kategori Uji']);
        $karyaDraft = Karya::create(['judul' => 'Karya Draft', 'kategori_id' => $kategori->id]);

        $response = $this->post(route('admin.karya.generate-ibid', $karyaDraft));

        $response->assertSessionHas('gagal');
        $this->assertNull($karyaDraft->fresh()->ibid_number);
    }

    public function test_unduh_qr_ditolak_untuk_karya_belum_ber_ibid(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Kategori Uji']);
        $karya = Karya::create(['judul' => 'Karya Uji', 'kategori_id' => $kategori->id]);

        $this->get(route('admin.karya.qr-svg', $karya))->assertNotFound();
        $this->get(route('admin.karya.qr-png', $karya))->assertNotFound();
    }

    public function test_unduh_qr_svg_dan_png_berhasil_dan_tercatat_di_audit_log(): void
    {
        $this->actingAs(User::factory()->create());
        $karya = $this->buatKaryaSiapIbid();
        $this->post(route('admin.karya.generate-ibid', $karya));
        $karya = $karya->fresh();

        $responseSvg = $this->get(route('admin.karya.qr-svg', $karya));
        $responseSvg->assertOk();
        $responseSvg->assertHeader('Content-Type', 'image/svg+xml');

        $responsePng = $this->get(route('admin.karya.qr-png', $karya));
        $responsePng->assertOk();
        $responsePng->assertHeader('Content-Type', 'image/png');

        $this->assertDatabaseHas('audit_log', ['aksi' => 'unduh_qr_svg', 'entitas_id' => $karya->id]);
        $this->assertDatabaseHas('audit_log', ['aksi' => 'unduh_qr_png', 'entitas_id' => $karya->id]);
    }
}
