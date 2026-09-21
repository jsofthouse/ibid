<?php

namespace Tests\Feature;

use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Models\Karya;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryaStatusMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_karya_baru_default_ke_draft_dan_belum_ber_ibid(): void
    {
        $kategori = Kategori::create(['nama' => 'Novel']);

        $karya = Karya::create([
            'judul' => 'Karya Uji',
            'kategori_id' => $kategori->id,
        ])->refresh();

        $this->assertSame(StatusProduksi::Draft, $karya->status_produksi);
        $this->assertSame(StatusIdentitas::BelumBerIbid, $karya->status_identitas);
        $this->assertTrue($karya->tampil_pra_terbit);
        $this->assertNull($karya->ibid_number);
        $this->assertNull($karya->tanggal_ibid);
        $this->assertNull($karya->tanggal_dipublikasikan);
    }

    public function test_status_produksi_status_identitas_dan_ibid_number_tidak_bisa_mass_assignment(): void
    {
        $kategori = Kategori::create(['nama' => 'Non-Fiksi']);

        $karya = Karya::create([
            'judul' => 'Karya Uji Mass Assignment',
            'kategori_id' => $kategori->id,
            'status_produksi' => StatusProduksi::Diterbitkan->value,
            'status_identitas' => StatusIdentitas::Dipublikasikan->value,
            'ibid_number' => 'IRF-2026-000001',
        ])->refresh();

        $this->assertSame(StatusProduksi::Draft, $karya->status_produksi);
        $this->assertSame(StatusIdentitas::BelumBerIbid, $karya->status_identitas);
        $this->assertNull($karya->ibid_number);
    }
}
