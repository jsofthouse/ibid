<?php

namespace Tests\Feature;

use App\Actions\GenerateIbidAction;
use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Exceptions\TransisiStatusException;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GenerateIbidActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateIbidAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(GenerateIbidAction::class);
    }

    private function buatKaryaSiapIbid(): Karya
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji '.uniqid()]);
        $penulis = Orang::create(['nama' => 'Penulis Uji '.uniqid()]);

        $karya = Karya::create(['judul' => 'Karya Uji', 'kategori_id' => $kategori->id]);
        $karya->forceFill(['status_produksi' => StatusProduksi::Disetujui])->save();
        $karya->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);

        return $karya->fresh();
    }

    public function test_format_nomor_ibid_sesuai_pola_dan_tahun_berjalan(): void
    {
        $karya = $this->buatKaryaSiapIbid();

        $hasil = $this->action->execute($karya);

        $this->assertMatchesRegularExpression('/^IRF-\d{4}-\d{6}$/', $hasil->ibid_number);
        $this->assertStringContainsString('IRF-'.now()->year.'-', $hasil->ibid_number);
        $this->assertSame('000001', substr($hasil->ibid_number, -6));
    }

    public function test_nomor_naik_berurutan_tanpa_duplikat_atau_loncat(): void
    {
        $nomor = [];

        for ($i = 0; $i < 5; $i++) {
            $karya = $this->buatKaryaSiapIbid();
            $hasil = $this->action->execute($karya);
            $nomor[] = (int) substr($hasil->ibid_number, -6);
        }

        $this->assertSame([1, 2, 3, 4, 5], $nomor);
        $this->assertSame(5, DB::table('ibid_counter')->value('nomor_terakhir'));
    }

    public function test_generate_mengubah_status_identitas_dan_mengisi_tanggal_ibid(): void
    {
        $karya = $this->buatKaryaSiapIbid();

        $hasil = $this->action->execute($karya);

        $this->assertSame(StatusIdentitas::IbidDiterbitkan, $hasil->fresh()->status_identitas);
        $this->assertNotNull($hasil->fresh()->tanggal_ibid);
    }

    public function test_generate_tercatat_di_audit_log(): void
    {
        $karya = $this->buatKaryaSiapIbid();

        $hasil = $this->action->execute($karya);

        $this->assertDatabaseHas('audit_log', [
            'aksi' => 'generate_ibid',
            'entitas' => 'karya',
            'entitas_id' => $hasil->id,
        ]);
    }

    public function test_karya_yang_sudah_ber_ibid_tidak_bisa_generate_ulang(): void
    {
        $karya = $this->buatKaryaSiapIbid();
        $this->action->execute($karya);

        $this->expectException(TransisiStatusException::class);

        $this->action->execute($karya->fresh());
    }

    public function test_produksi_masih_draft_tidak_bisa_generate(): void
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji']);
        $karya = Karya::create(['judul' => 'Karya Draft', 'kategori_id' => $kategori->id]);

        $this->expectException(TransisiStatusException::class);

        $this->action->execute($karya);
    }

    public function test_tanpa_penulis_tidak_bisa_generate(): void
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji']);
        $karya = Karya::create(['judul' => 'Karya Tanpa Penulis', 'kategori_id' => $kategori->id]);
        $karya->forceFill(['status_produksi' => StatusProduksi::Disetujui])->save();

        $this->expectException(TransisiStatusException::class);
        $this->expectExceptionMessage('minimal satu penulis');

        $this->action->execute($karya->fresh());
    }
}
