<?php

namespace Tests\Feature;

use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Models\Karya;
use App\Models\Kategori;
use App\Models\Orang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    private function buatKarya(Kategori $kategori, array $atribut = []): Karya
    {
        $karya = Karya::create(array_merge(['judul' => 'Karya '.uniqid(), 'kategori_id' => $kategori->id], $atribut));

        if (isset($atribut['status_produksi']) || isset($atribut['status_identitas']) || isset($atribut['ibid_number'])) {
            $karya->forceFill($atribut)->save();
        }

        return $karya->fresh();
    }

    public function test_statistik_dasar_terhitung_benar(): void
    {
        $this->actingAs(User::factory()->create());
        $novel = Kategori::create(['nama' => 'Novel']);
        $puisi = Kategori::create(['nama' => 'Puisi']);

        $this->buatKarya($novel, ['isbn' => '978-1']);
        $this->buatKarya($novel);
        $this->buatKarya($puisi, [
            'ibid_number' => 'IRF-'.now()->year.'-000001',
            'status_identitas' => StatusIdentitas::IbidDiterbitkan,
            'tanggal_ibid' => now()->toDateString(),
        ]);
        $this->buatKarya($puisi, [
            'ibid_number' => 'IRF-2020-000001',
            'status_identitas' => StatusIdentitas::TidakAktif,
            'tanggal_ibid' => '2020-01-01',
        ]);
        Orang::create(['nama' => 'Penulis A']);
        Orang::create(['nama' => 'Penulis B']);

        $response = $this->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('totalKarya', 4);
        $response->assertViewHas('totalIbid', 2);
        $response->assertViewHas('ibidTahunBerjalan', 1);
        $response->assertViewHas('jumlahOrang', 2);
        $response->assertViewHas('denganIsbn', 1);
        $response->assertViewHas('tanpaIsbn', 3);
    }

    public function test_breakdown_per_kategori_dan_status(): void
    {
        $this->actingAs(User::factory()->create());
        $novel = Kategori::create(['nama' => 'Novel']);
        $puisi = Kategori::create(['nama' => 'Puisi']);

        $this->buatKarya($novel);
        $this->buatKarya($novel, ['status_produksi' => StatusProduksi::Disetujui]);
        $this->buatKarya($puisi, ['status_produksi' => StatusProduksi::Diterbitkan]);

        $response = $this->get('/admin/dashboard');

        $perKategori = $response->viewData('perKategori')->keyBy('kategori');
        $this->assertSame(2, (int) $perKategori['Novel']->total);
        $this->assertSame(1, (int) $perKategori['Puisi']->total);

        $perStatusProduksi = $response->viewData('perStatusProduksi')->keyBy(fn ($baris) => $baris->status_produksi->value);
        $this->assertSame(1, (int) $perStatusProduksi['draft']->total);
        $this->assertSame(1, (int) $perStatusProduksi['disetujui']->total);
        $this->assertSame(1, (int) $perStatusProduksi['diterbitkan']->total);
    }

    public function test_jumlah_query_tidak_bertambah_seiring_jumlah_karya_tanpa_n_plus_1(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Novel']);
        $this->buatKarya($kategori);
        $this->buatKarya($kategori);

        DB::enableQueryLog();
        $this->get('/admin/dashboard');
        $jumlahQuerySedikit = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        for ($i = 0; $i < 20; $i++) {
            $this->buatKarya($kategori);
        }

        DB::enableQueryLog();
        $this->get('/admin/dashboard');
        $jumlahQueryBanyak = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($jumlahQuerySedikit, $jumlahQueryBanyak);
    }
}
