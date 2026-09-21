<?php

namespace Tests\Feature;

use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Exceptions\TransisiStatusException;
use App\Models\AuditLog;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use App\Services\StatusKaryaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusKaryaServiceTest extends TestCase
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

        return Karya::create(array_merge([
            'judul' => 'Karya Uji',
            'kategori_id' => $kategori->id,
        ], $atribut));
    }

    private function jadikanBerIbid(Karya $karya, StatusIdentitas $status = StatusIdentitas::IbidDiterbitkan): Karya
    {
        $karya->forceFill([
            'ibid_number' => 'IRF-2026-000001',
            'status_identitas' => $status,
            'tanggal_ibid' => now()->toDateString(),
        ])->save();

        return $karya->fresh();
    }

    public function test_transisi_maju_tanpa_alasan_berhasil(): void
    {
        $karya = $this->buatKarya();

        $hasil = $this->service->ubahStatusProduksi($karya, StatusProduksi::Disetujui);

        $this->assertSame(StatusProduksi::Disetujui, $hasil->fresh()->status_produksi);
        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'ubah_status_produksi',
            'entitas' => 'karya',
            'entitas_id' => $karya->id,
            'keterangan' => null,
        ]);
    }

    public function test_pindah_ke_diterbitkan_mengisi_tanggal_diterbitkan_otomatis(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['status_produksi' => StatusProduksi::DalamProses])->save();

        $this->assertNull($karya->fresh()->tanggal_diterbitkan);

        $hasil = $this->service->ubahStatusProduksi($karya->fresh(), StatusProduksi::Diterbitkan);

        $this->assertSame(now()->toDateString(), $hasil->fresh()->tanggal_diterbitkan->toDateString());
    }

    public function test_pindah_ke_disetujui_otomatis_generate_ibid_kalau_data_lengkap(): void
    {
        $karya = $this->buatKarya();
        $penulis = Orang::create(['nama' => 'Penulis Uji']);
        $karya->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);

        $hasil = $this->service->ubahStatusProduksi($karya->fresh(), StatusProduksi::Disetujui);

        $this->assertSame(StatusProduksi::Disetujui, $hasil->status_produksi);
        $this->assertSame(StatusIdentitas::IbidDiterbitkan, $hasil->status_identitas);
        $this->assertNotNull($hasil->ibid_number);
        $this->assertDatabaseHas('audit_log', ['aksi' => 'ubah_status_produksi', 'entitas_id' => $karya->id]);
        $this->assertDatabaseHas('audit_log', ['aksi' => 'generate_ibid', 'entitas_id' => $karya->id]);
    }

    public function test_pindah_ke_disetujui_tanpa_penulis_tetap_berhasil_tapi_ibid_belum_terbit(): void
    {
        $karya = $this->buatKarya();

        $hasil = $this->service->ubahStatusProduksi($karya, StatusProduksi::Disetujui);

        $this->assertSame(StatusProduksi::Disetujui, $hasil->status_produksi);
        $this->assertSame(StatusIdentitas::BelumBerIbid, $hasil->status_identitas);
        $this->assertNull($hasil->ibid_number);
    }

    public function test_lompat_status_ditolak(): void
    {
        $karya = $this->buatKarya();

        $this->expectException(TransisiStatusException::class);

        $this->service->ubahStatusProduksi($karya, StatusProduksi::DalamProses);
    }

    public function test_mundur_tanpa_alasan_ditolak(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['status_produksi' => StatusProduksi::DalamProses])->save();

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('Alasan wajib');

        $this->service->ubahStatusProduksi($karya, StatusProduksi::Disetujui);
    }

    public function test_mundur_dengan_alasan_berhasil_dan_tercatat(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['status_produksi' => StatusProduksi::DalamProses])->save();

        $this->service->ubahStatusProduksi($karya, StatusProduksi::Disetujui, 'Naskah perlu revisi ulang.');

        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'ubah_status_produksi',
            'keterangan' => 'Naskah perlu revisi ulang.',
        ]);
    }

    public function test_ubah_status_produksi_tidak_menghasilkan_log_ganda_dari_observer(): void
    {
        $karya = $this->buatKarya();

        $this->service->ubahStatusProduksi($karya, StatusProduksi::Disetujui);

        $jumlahLogUbahGenerik = AuditLog::query()
            ->where('entitas', 'karya')
            ->where('entitas_id', $karya->id)
            ->where('aksi', 'ubah')
            ->count();

        $this->assertSame(0, $jumlahLogUbahGenerik);
    }

    public function test_target_dibatalkan_ditolak_harus_lewat_aksi_khusus(): void
    {
        $karya = $this->buatKarya();

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('Batalkan Penerbitan');

        $this->service->ubahStatusProduksi($karya, StatusProduksi::Dibatalkan);
    }

    public function test_invarian_i2_karya_ber_ibid_tidak_bisa_kembali_draft(): void
    {
        $karya = $this->buatKarya();
        $karya = $this->jadikanBerIbid($karya);

        $this->expectException(TransisiStatusException::class);

        $this->service->ubahStatusProduksi($karya, StatusProduksi::Draft, 'coba mundur');
    }

    public function test_produksi_tidak_boleh_mundur_dari_diterbitkan_saat_dipublikasikan(): void
    {
        $karya = $this->buatKarya([
            'tahun_terbit' => 2026, 'kota_terbit' => 'Jakarta', 'edisi' => '1',
            'bahasa' => 'Indonesia', 'jumlah_halaman' => 100, 'ukuran' => '14x21', 'cover_path' => 'cover/x.jpg',
        ]);
        $karya->forceFill(['status_produksi' => StatusProduksi::Diterbitkan])->save();
        $karya = $this->jadikanBerIbid($karya, StatusIdentitas::Dipublikasikan);

        $this->expectException(TransisiStatusException::class);

        $this->service->ubahStatusProduksi($karya, StatusProduksi::DalamProses, 'coba mundur');
    }

    public function test_produksi_boleh_mundur_dari_diterbitkan_saat_belum_dipublikasikan(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['status_produksi' => StatusProduksi::Diterbitkan])->save();
        $karya = $this->jadikanBerIbid($karya, StatusIdentitas::IbidDiterbitkan);

        $hasil = $this->service->ubahStatusProduksi($karya, StatusProduksi::DalamProses, 'revisi cover');

        $this->assertSame(StatusProduksi::DalamProses, $hasil->fresh()->status_produksi);
    }

    public function test_ubah_status_identitas_ditolak_kalau_masih_belum_ber_ibid(): void
    {
        $karya = $this->buatKarya();

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('Generate IBID');

        $this->service->ubahStatusIdentitas($karya, StatusIdentitas::IbidDiterbitkan);
    }

    public function test_publikasikan_ditolak_kalau_data_belum_lengkap(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['status_produksi' => StatusProduksi::Diterbitkan])->save();
        $karya = $this->jadikanBerIbid($karya);

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('belum lengkap');

        $this->service->ubahStatusIdentitas($karya, StatusIdentitas::Dipublikasikan);
    }

    public function test_publikasikan_berhasil_kalau_data_lengkap_dan_mengisi_tanggal_dipublikasikan(): void
    {
        $karya = $this->buatKarya([
            'tahun_terbit' => 2026, 'kota_terbit' => 'Jakarta', 'edisi' => '1',
            'bahasa' => 'Indonesia', 'jumlah_halaman' => 100, 'ukuran' => '14x21', 'cover_path' => 'cover/x.jpg',
        ]);
        $karya->forceFill(['status_produksi' => StatusProduksi::Diterbitkan])->save();
        $karya = $this->jadikanBerIbid($karya);

        $hasil = $this->service->ubahStatusIdentitas($karya, StatusIdentitas::Dipublikasikan);

        $this->assertSame(StatusIdentitas::Dipublikasikan, $hasil->fresh()->status_identitas);
        $this->assertNotNull($hasil->fresh()->tanggal_dipublikasikan);
    }

    public function test_nonaktifkan_tanpa_alasan_ditolak(): void
    {
        $karya = $this->buatKarya();
        $karya = $this->jadikanBerIbid($karya);

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('Alasan wajib');

        $this->service->ubahStatusIdentitas($karya, StatusIdentitas::TidakAktif);
    }

    public function test_reaktivasi_ditolak_kalau_produksi_dibatalkan(): void
    {
        $karya = $this->buatKarya();
        $karya = $this->jadikanBerIbid($karya, StatusIdentitas::TidakAktif);
        $karya->forceFill(['status_produksi' => StatusProduksi::Dibatalkan])->save();

        $this->expectException(TransisiStatusException::class);

        $this->service->ubahStatusIdentitas($karya->fresh(), StatusIdentitas::IbidDiterbitkan);
    }

    public function test_batalkan_penerbitan_ditolak_untuk_karya_yang_sudah_diterbitkan(): void
    {
        $karya = $this->buatKarya();
        $karya->forceFill(['status_produksi' => StatusProduksi::Diterbitkan])->save();

        $this->expectException(TransisiStatusException::class);

        $this->service->batalkanPenerbitan($karya->fresh(), 'batal', null);
    }

    public function test_batalkan_penerbitan_wajib_alasan(): void
    {
        $karya = $this->buatKarya();

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('Alasan pembatalan wajib');

        $this->service->batalkanPenerbitan($karya, '', null);
    }

    public function test_batalkan_penerbitan_untuk_karya_ber_ibid_wajib_konfirmasi_nomor_yang_cocok(): void
    {
        $karya = $this->buatKarya();
        $karya = $this->jadikanBerIbid($karya);

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('Konfirmasi nomor IBID tidak cocok');

        $this->service->batalkanPenerbitan($karya, 'batal', 'IRF-2026-999999');
    }

    public function test_batalkan_penerbitan_efek_atomik_invarian_i5(): void
    {
        $karya = $this->buatKarya();
        $karya = $this->jadikanBerIbid($karya);

        $hasil = $this->service->batalkanPenerbitan($karya, 'penulis mengundurkan diri', 'IRF-2026-000001');

        $this->assertSame(StatusProduksi::Dibatalkan, $hasil->fresh()->status_produksi);
        $this->assertSame(StatusIdentitas::TidakAktif, $hasil->fresh()->status_identitas);
        $this->assertSame('IRF-2026-000001', $hasil->fresh()->ibid_number, 'nomor IBID tidak boleh hangus dari record, hanya jadi tidak aktif');
    }

    public function test_batalkan_penerbitan_karya_belum_ber_ibid_tidak_mengubah_identitas(): void
    {
        $karya = $this->buatKarya();

        $hasil = $this->service->batalkanPenerbitan($karya, 'salah input', null);

        $this->assertSame(StatusProduksi::Dibatalkan, $hasil->fresh()->status_produksi);
        $this->assertSame(StatusIdentitas::BelumBerIbid, $hasil->fresh()->status_identitas);
    }
}
