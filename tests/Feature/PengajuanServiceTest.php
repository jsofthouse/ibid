<?php

namespace Tests\Feature;

use App\Enums\PeranOrang;
use App\Enums\StatusPengajuan;
use App\Enums\StatusProduksi;
use App\Exceptions\TransisiStatusException;
use App\Models\AuditLog;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Pengajuan;
use App\Services\PengajuanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanServiceTest extends TestCase
{
    use RefreshDatabase;

    private PengajuanService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PengajuanService::class);
    }

    private function buatPengajuan(array $atribut = []): Pengajuan
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji '.uniqid()]);

        return Pengajuan::create(array_merge([
            'nama' => 'Penulis Uji',
            'nama_pena' => 'Pena Uji',
            'email' => 'penulis@example.com',
            'judul' => 'Naskah Uji',
            'kategori_id' => $kategori->id,
            'sinopsis' => 'Sinopsis singkat.',
            'naskah_path' => 'naskah/'.uniqid().'.pdf',
        ], $atribut));
    }

    public function test_transisi_maju_berhasil_dan_tercatat(): void
    {
        $pengajuan = $this->buatPengajuan();

        $hasil = $this->service->ubahStatus($pengajuan, StatusPengajuan::Diproses);

        $this->assertSame(StatusPengajuan::Diproses, $hasil->fresh()->status);
        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'proses_pengajuan',
            'entitas' => 'pengajuan',
            'entitas_id' => $pengajuan->id,
        ]);
    }

    public function test_lompat_status_ditolak(): void
    {
        $pengajuan = $this->buatPengajuan();

        $this->expectException(TransisiStatusException::class);

        $this->service->ubahStatus($pengajuan, StatusPengajuan::Disetujui);
    }

    public function test_tolak_tanpa_catatan_admin_ditolak(): void
    {
        $pengajuan = $this->buatPengajuan();

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('Catatan admin wajib');

        $this->service->ubahStatus($pengajuan, StatusPengajuan::Ditolak);
    }

    public function test_tolak_dengan_catatan_admin_berhasil_dan_tercatat(): void
    {
        $pengajuan = $this->buatPengajuan();

        $hasil = $this->service->ubahStatus($pengajuan, StatusPengajuan::Ditolak, 'Naskah tidak sesuai kriteria.');

        $this->assertSame(StatusPengajuan::Ditolak, $hasil->fresh()->status);
        $this->assertSame('Naskah tidak sesuai kriteria.', $hasil->fresh()->catatan_admin);
        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'tolak_pengajuan',
            'entitas' => 'pengajuan',
            'entitas_id' => $pengajuan->id,
            'keterangan' => 'Naskah tidak sesuai kriteria.',
        ]);
    }

    public function test_setujui_convert_ke_karya_dengan_data_ter_prefill(): void
    {
        $pengajuan = $this->buatPengajuan();
        $pengajuan = $this->service->ubahStatus($pengajuan, StatusPengajuan::Diproses);

        $hasil = $this->service->ubahStatus($pengajuan, StatusPengajuan::Disetujui);

        $karya = $hasil->fresh()->karya;

        $this->assertNotNull($karya);
        $this->assertSame('Naskah Uji', $karya->judul);
        $this->assertSame($pengajuan->kategori_id, $karya->kategori_id);
        $this->assertSame('Sinopsis singkat.', $karya->sinopsis);
        $this->assertSame(StatusProduksi::Disetujui, $karya->status_produksi);
        $this->assertNull($karya->ibid_number);

        $penulis = $karya->daftarOrang->first();
        $this->assertNotNull($penulis);
        $this->assertSame('Penulis Uji', $penulis->nama);
        $this->assertSame('Pena Uji', $penulis->nama_pena);
        $this->assertSame(PeranOrang::Penulis, $penulis->pivot->role);

        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'setujui_pengajuan',
            'entitas' => 'pengajuan',
            'entitas_id' => $pengajuan->id,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'convert_pengajuan_ke_karya',
            'entitas' => 'pengajuan',
            'entitas_id' => $pengajuan->id,
        ]);
    }

    public function test_convert_memberi_declared_role_penulis_pada_orang_baru(): void
    {
        $pengajuan = $this->buatPengajuan();
        $pengajuan = $this->service->ubahStatus($pengajuan, StatusPengajuan::Diproses);

        $hasil = $this->service->ubahStatus($pengajuan, StatusPengajuan::Disetujui);

        $penulis = $hasil->fresh()->karya->daftarOrang->first();

        $this->assertDatabaseHas('orang_role', ['orang_id' => $penulis->id, 'role' => 'penulis']);
        $this->assertDatabaseCount('orang_role', 1);
    }

    public function test_guard_tidak_bisa_convert_ulang_pengajuan_yang_sudah_punya_karya(): void
    {
        $pengajuan = $this->buatPengajuan();
        $pengajuan = $this->service->ubahStatus($pengajuan, StatusPengajuan::Diproses);
        $pengajuan = $this->service->ubahStatus($pengajuan, StatusPengajuan::Disetujui);

        $jumlahKaryaSebelum = Karya::count();

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('sudah dikonversi');

        try {
            $this->service->ubahStatus($pengajuan->fresh(), StatusPengajuan::Ditolak, 'coba tolak ulang');
        } finally {
            $this->assertSame($jumlahKaryaSebelum, Karya::count());
        }
    }

    public function test_ubah_status_tidak_menghasilkan_log_ganda_dari_observer(): void
    {
        $pengajuan = $this->buatPengajuan();

        $this->service->ubahStatus($pengajuan, StatusPengajuan::Diproses);

        $jumlahLogUbahGenerik = AuditLog::query()
            ->where('entitas', 'pengajuan')
            ->where('entitas_id', $pengajuan->id)
            ->where('aksi', 'ubah')
            ->count();

        $this->assertSame(0, $jumlahLogUbahGenerik);
    }
}
