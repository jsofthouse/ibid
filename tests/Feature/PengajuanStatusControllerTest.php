<?php

namespace Tests\Feature;

use App\Enums\StatusPengajuan;
use App\Models\Kategori;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanStatusControllerTest extends TestCase
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

    public function test_guest_tidak_bisa_ubah_status(): void
    {
        $pengajuan = $this->buatPengajuan();

        $this->post(route('admin.pengajuan.status', $pengajuan), ['status' => 'diproses'])
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_bisa_memproses_pengajuan(): void
    {
        $this->actingAs(User::factory()->create());
        $pengajuan = $this->buatPengajuan();

        $response = $this->post(route('admin.pengajuan.status', $pengajuan), ['status' => 'diproses']);

        $response->assertRedirect(route('admin.pengajuan.show', $pengajuan));
        $response->assertSessionHas('sukses');
        $this->assertSame(StatusPengajuan::Diproses, $pengajuan->fresh()->status);
    }

    public function test_tolak_tanpa_catatan_admin_ditolak_form_request(): void
    {
        $this->actingAs(User::factory()->create());
        $pengajuan = $this->buatPengajuan();

        $this->post(route('admin.pengajuan.status', $pengajuan), ['status' => 'ditolak'])
            ->assertSessionHasErrors('catatan_admin');

        $this->assertSame(StatusPengajuan::Baru, $pengajuan->fresh()->status);
    }

    public function test_setujui_pengajuan_convert_ke_karya_lewat_http(): void
    {
        $this->actingAs(User::factory()->create());
        $pengajuan = $this->buatPengajuan();
        $pengajuan->forceFill(['status' => StatusPengajuan::Diproses])->save();

        $response = $this->post(route('admin.pengajuan.status', $pengajuan), ['status' => 'disetujui']);

        $response->assertSessionHas('sukses', fn ($pesan) => str_contains($pesan, 'dikonversi'));
        $this->assertNotNull($pengajuan->fresh()->karya_id);
    }

    public function test_lompat_status_diflash_sebagai_gagal(): void
    {
        $this->actingAs(User::factory()->create());
        $pengajuan = $this->buatPengajuan();

        $response = $this->post(route('admin.pengajuan.status', $pengajuan), ['status' => 'disetujui']);

        $response->assertRedirect(route('admin.pengajuan.show', $pengajuan));
        $response->assertSessionHas('gagal');
        $this->assertSame(StatusPengajuan::Baru, $pengajuan->fresh()->status);
    }

    public function test_convert_ulang_pengajuan_yang_sudah_punya_karya_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        $pengajuan = $this->buatPengajuan();
        $pengajuan->forceFill(['status' => StatusPengajuan::Diproses])->save();
        $this->post(route('admin.pengajuan.status', $pengajuan), ['status' => 'disetujui']);

        $response = $this->post(route('admin.pengajuan.status', $pengajuan->fresh()), [
            'status' => 'ditolak',
            'catatan_admin' => 'coba tolak setelah convert',
        ]);

        $response->assertSessionHas('gagal');
    }
}
