<?php

namespace Tests\Feature;

use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_render_dengan_statistik_kosong(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewHas('jumlahKaryaTerdaftar', 0)
            ->assertViewHas('jumlahPenulis', 0)
            ->assertViewHas('jumlahKategori', 0);
    }

    public function test_statistik_beranda_sesuai_data_asli_bukan_hardcode(): void
    {
        $novel = Kategori::create(['nama' => 'Novel']);
        Kategori::create(['nama' => 'Puisi']);

        $karyaBerIbid = Karya::create(['judul' => 'Karya Ber-IBID', 'kategori_id' => $novel->id]);
        $karyaBerIbid->forceFill([
            'ibid_number' => 'IRF-2026-000001',
            'status_identitas' => StatusIdentitas::IbidDiterbitkan,
        ])->save();

        // Belum ber-IBID - tidak boleh ikut terhitung sebagai "Karya Terdaftar".
        Karya::create(['judul' => 'Karya Draft', 'kategori_id' => $novel->id]);

        $penulis = Orang::create(['nama' => 'Penulis Satu']);
        $editor = Orang::create(['nama' => 'Bukan Penulis']);
        $karyaBerIbid->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);
        $karyaBerIbid->daftarOrang()->attach($editor->id, ['role' => PeranOrang::Editor->value]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewHas('jumlahKaryaTerdaftar', 1);
        $response->assertViewHas('jumlahPenulis', 1);
        $response->assertViewHas('jumlahKategori', 2);
    }

    public function test_penulis_yang_sama_di_banyak_karya_dihitung_sekali(): void
    {
        $kategori = Kategori::create(['nama' => 'Novel']);
        $penulis = Orang::create(['nama' => 'Penulis Produktif']);

        foreach (range(1, 3) as $i) {
            $karya = Karya::create(['judul' => "Karya $i", 'kategori_id' => $kategori->id]);
            $karya->forceFill([
                'ibid_number' => "IRF-2026-00000{$i}",
                'status_identitas' => StatusIdentitas::IbidDiterbitkan,
            ])->save();
            $karya->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);
        }

        $this->get('/')
            ->assertOk()
            ->assertViewHas('jumlahKaryaTerdaftar', 3)
            ->assertViewHas('jumlahPenulis', 1);
    }
}
