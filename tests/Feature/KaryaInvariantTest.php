<?php

namespace Tests\Feature;

use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Exceptions\TransisiStatusException;
use App\Models\Karya;
use App\Models\Kategori;
use App\Services\StatusKaryaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test eksplisit untuk invarian I1-I5 di docs/PRD.md §5 dan
 * docs/rencana-fase-2.md. Sebagian guard yang sama juga sudah tercakup
 * skenario per-kasus di StatusKaryaServiceTest - file ini fokus memetakan
 * tiap invarian ke satu test yang jelas namanya.
 */
class KaryaInvariantTest extends TestCase
{
    use RefreshDatabase;

    private StatusKaryaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StatusKaryaService::class);
    }

    private function buatKarya(array $atribut = []): Karya
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji '.uniqid()]);

        return Karya::create(array_merge(['judul' => 'Karya Uji', 'kategori_id' => $kategori->id], $atribut))->fresh();
    }

    public function test_i1_ibid_number_null_ekuivalen_belum_ber_ibid(): void
    {
        $karya = $this->buatKarya();

        $this->assertNull($karya->ibid_number);
        $this->assertSame(StatusIdentitas::BelumBerIbid, $karya->status_identitas);

        $karya->forceFill(['ibid_number' => 'IRF-2026-000001', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();
        $karya = $karya->fresh();

        $this->assertNotNull($karya->ibid_number);
        $this->assertNotSame(StatusIdentitas::BelumBerIbid, $karya->status_identitas);
    }

    public function test_i2_karya_ber_ibid_tidak_pernah_berstatus_produksi_draft(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['ibid_number' => 'IRF-2026-000001', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();

        $this->expectException(TransisiStatusException::class);

        $this->service->ubahStatusProduksi($karya->fresh(), StatusProduksi::Draft, 'coba mundur ke draft');
    }

    public function test_i3_dipublikasikan_hanya_kalau_produksi_diterbitkan_dan_cover_terisi(): void
    {
        $karyaTanpaCover = $this->buatKarya([
            'tahun_terbit' => 2026, 'kota_terbit' => 'Jakarta', 'edisi' => '1',
            'bahasa' => 'Indonesia', 'jumlah_halaman' => 100, 'ukuran' => '14x21',
        ]);
        $karyaTanpaCover->forceFill(['status_produksi' => StatusProduksi::Diterbitkan])->save();
        $karyaTanpaCover->forceFill(['ibid_number' => 'IRF-2026-000002', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();

        try {
            $this->service->ubahStatusIdentitas($karyaTanpaCover->fresh(), StatusIdentitas::Dipublikasikan);
            $this->fail('Harusnya gagal karena cover belum terisi.');
        } catch (TransisiStatusException) {
            // diharapkan
        }

        $this->assertNotSame(StatusIdentitas::Dipublikasikan, $karyaTanpaCover->fresh()->status_identitas);

        $karyaLengkap = $this->buatKarya([
            'tahun_terbit' => 2026, 'kota_terbit' => 'Jakarta', 'edisi' => '1',
            'bahasa' => 'Indonesia', 'jumlah_halaman' => 100, 'ukuran' => '14x21', 'cover_path' => 'cover/x.jpg',
        ]);
        $karyaLengkap->forceFill(['status_produksi' => StatusProduksi::Diterbitkan])->save();
        $karyaLengkap->forceFill(['ibid_number' => 'IRF-2026-000003', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();

        $hasil = $this->service->ubahStatusIdentitas($karyaLengkap->fresh(), StatusIdentitas::Dipublikasikan);

        $this->assertSame(StatusIdentitas::Dipublikasikan, $hasil->fresh()->status_identitas);
        $this->assertSame(StatusProduksi::Diterbitkan, $hasil->fresh()->status_produksi);
        $this->assertNotEmpty($hasil->fresh()->cover_path);
    }

    public function test_i4_ibid_number_tidak_bisa_diubah_lewat_mass_assignment(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['ibid_number' => 'IRF-2026-000001', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();

        $karya->update(['ibid_number' => 'IRF-2026-999999', 'judul' => 'Judul Baru']);

        $this->assertSame('IRF-2026-000001', $karya->fresh()->ibid_number);
        $this->assertSame('Judul Baru', $karya->fresh()->judul);
    }

    public function test_i4_nomor_ibid_tetap_tercatat_setelah_dibatalkan_tidak_hangus_dari_record(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['ibid_number' => 'IRF-2026-000001', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();

        $hasil = $this->service->batalkanPenerbitan($karya->fresh(), 'batal', 'IRF-2026-000001');

        $this->assertSame('IRF-2026-000001', $hasil->fresh()->ibid_number);
    }

    public function test_i5_dibatalkan_dan_ber_ibid_maka_identitas_jadi_tidak_aktif(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['ibid_number' => 'IRF-2026-000001', 'status_identitas' => StatusIdentitas::IbidDiterbitkan])->save();

        $hasil = $this->service->batalkanPenerbitan($karya->fresh(), 'batal', 'IRF-2026-000001');

        $this->assertSame(StatusProduksi::Dibatalkan, $hasil->fresh()->status_produksi);
        $this->assertContains($hasil->fresh()->status_identitas, [StatusIdentitas::TidakAktif, StatusIdentitas::Diarsipkan]);
    }

    public function test_i5_dibatalkan_dan_belum_ber_ibid_identitas_tidak_perlu_berubah(): void
    {
        $karya = $this->buatKarya();

        $hasil = $this->service->batalkanPenerbitan($karya, 'batal', null);

        $this->assertSame(StatusProduksi::Dibatalkan, $hasil->fresh()->status_produksi);
        $this->assertSame(StatusIdentitas::BelumBerIbid, $hasil->fresh()->status_identitas);
    }
}
