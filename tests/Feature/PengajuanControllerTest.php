<?php

namespace Tests\Feature;

use App\Enums\StatusPengajuan;
use App\Models\Kategori;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanControllerTest extends TestCase
{
    use RefreshDatabase;

    private function buatPengajuan(array $atribut = []): Pengajuan
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji '.uniqid()]);

        return Pengajuan::create(array_merge([
            'nama' => 'Penulis Uji',
            'email' => 'penulis@example.com',
            'judul' => 'Naskah Uji',
            'kategori_id' => $kategori->id,
            'naskah_path' => 'naskah/'.uniqid().'.pdf',
        ], $atribut));
    }

    public function test_guest_tidak_bisa_akses_daftar_pengajuan(): void
    {
        $this->get('/admin/pengajuan')->assertRedirect(route('admin.login'));
    }

    public function test_guest_tidak_bisa_akses_detail_pengajuan(): void
    {
        $pengajuan = $this->buatPengajuan();

        $this->get("/admin/pengajuan/{$pengajuan->id}")->assertRedirect(route('admin.login'));
    }

    public function test_admin_bisa_melihat_daftar_pengajuan(): void
    {
        $this->actingAs(User::factory()->create());
        $this->buatPengajuan(['judul' => 'Naskah Pertama']);

        $this->get('/admin/pengajuan')->assertOk()->assertSee('Naskah Pertama');
    }

    public function test_filter_status_hanya_menampilkan_status_terpilih(): void
    {
        $this->actingAs(User::factory()->create());
        $baru = $this->buatPengajuan(['judul' => 'Pengajuan Baru Saja']);
        $ditolak = $this->buatPengajuan(['judul' => 'Pengajuan Ditolak', 'status' => StatusPengajuan::Ditolak->value]);

        $response = $this->get('/admin/pengajuan?status=baru');

        $response->assertOk()->assertSee('Pengajuan Baru Saja')->assertDontSee('Pengajuan Ditolak');
    }

    public function test_whitelist_urutkan_menolak_kolom_tidak_dikenal(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/pengajuan?urutkan=email')->assertSessionHasErrors('urutkan');
    }

    public function test_whitelist_status_menolak_nilai_tidak_dikenal(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/pengajuan?status=aneh')->assertSessionHasErrors('status');
    }

    public function test_admin_bisa_melihat_detail_pengajuan_dengan_link_unduh(): void
    {
        $this->actingAs(User::factory()->create());
        $pengajuan = $this->buatPengajuan(['naskah_path' => 'naskah/berkas-uji.pdf']);

        $this->get("/admin/pengajuan/{$pengajuan->id}")
            ->assertOk()
            ->assertSee(route('admin.berkas', ['path' => 'naskah/berkas-uji.pdf']), escape: false);
    }
}
