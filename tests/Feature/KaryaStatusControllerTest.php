<?php

namespace Tests\Feature;

use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryaStatusControllerTest extends TestCase
{
    use RefreshDatabase;

    private function buatKarya(): Karya
    {
        $kategori = Kategori::create(['nama' => 'Novel']);

        return Karya::create(['judul' => 'Karya Uji', 'kategori_id' => $kategori->id]);
    }

    public function test_guest_tidak_bisa_ubah_status(): void
    {
        $karya = $this->buatKarya();

        $this->post(route('admin.karya.status-produksi', $karya), ['status_produksi' => 'disetujui'])
            ->assertRedirect(route('admin.login'));
    }

    public function test_ubah_status_produksi_berhasil_lewat_http(): void
    {
        $this->actingAs(User::factory()->create());
        $karya = $this->buatKarya();

        $response = $this->post(route('admin.karya.status-produksi', $karya), [
            'status_produksi' => StatusProduksi::Disetujui->value,
        ]);

        $response->assertRedirect(route('admin.karya.show', $karya));
        $response->assertSessionHas('sukses');
        $this->assertSame(StatusProduksi::Disetujui, $karya->fresh()->status_produksi);
    }

    public function test_pesan_sukses_menyebutkan_nomor_ibid_kalau_otomatis_tergenerate(): void
    {
        $this->actingAs(User::factory()->create());
        $karya = $this->buatKarya();
        $penulis = Orang::create(['nama' => 'Penulis Uji']);
        $karya->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);

        $response = $this->post(route('admin.karya.status-produksi', $karya), [
            'status_produksi' => StatusProduksi::Disetujui->value,
        ]);

        $response->assertSessionHas('sukses', fn ($pesan) => str_contains($pesan, 'otomatis digenerate')
            && str_contains($pesan, $karya->fresh()->ibid_number));
    }

    public function test_ubah_status_produksi_mundur_tanpa_alasan_ditolak_form_request(): void
    {
        $this->actingAs(User::factory()->create());
        $karya = $this->buatKarya();
        $karya->forceFill(['status_produksi' => StatusProduksi::DalamProses])->save();

        $response = $this->post(route('admin.karya.status-produksi', $karya), [
            'status_produksi' => StatusProduksi::Disetujui->value,
        ]);

        $response->assertSessionHasErrors('alasan');
        $this->assertSame(StatusProduksi::DalamProses, $karya->fresh()->status_produksi);
    }

    public function test_transisi_ilegal_dari_service_diflash_sebagai_gagal(): void
    {
        $this->actingAs(User::factory()->create());
        $karya = $this->buatKarya();

        $response = $this->post(route('admin.karya.status-produksi', $karya), [
            'status_produksi' => StatusProduksi::DalamProses->value,
        ]);

        $response->assertRedirect(route('admin.karya.show', $karya));
        $response->assertSessionHas('gagal');
        $this->assertSame(StatusProduksi::Draft, $karya->fresh()->status_produksi);
    }

    public function test_batalkan_penerbitan_lewat_http(): void
    {
        $this->actingAs(User::factory()->create());
        $karya = $this->buatKarya();

        $response = $this->post(route('admin.karya.batalkan-penerbitan', $karya), [
            'alasan' => 'Penulis membatalkan',
        ]);

        $response->assertRedirect(route('admin.karya.show', $karya));
        $this->assertSame(StatusProduksi::Dibatalkan, $karya->fresh()->status_produksi);
    }

    public function test_batalkan_penerbitan_tanpa_alasan_ditolak_form_request(): void
    {
        $this->actingAs(User::factory()->create());
        $karya = $this->buatKarya();

        $this->post(route('admin.karya.batalkan-penerbitan', $karya), [])
            ->assertSessionHasErrors('alasan');
    }

    public function test_ubah_status_identitas_wajib_konfirmasi_saat_ber_ibid(): void
    {
        $this->actingAs(User::factory()->create());
        $karya = $this->buatKarya();
        $karya->forceFill(['ibid_number' => 'IRF-2026-000001', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();

        $this->post(route('admin.karya.batalkan-penerbitan', $karya), ['alasan' => 'batal'])
            ->assertSessionHasErrors('konfirmasi_nomor_ibid');
    }
}
