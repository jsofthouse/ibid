<?php

namespace Tests\Feature;

use App\Enums\StatusPengajuan;
use App\Mail\PengajuanDiterimaMail;
use App\Models\Kategori;
use App\Models\Pengajuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AjukanPenerbitanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function naskahValid(string $nama = 'naskah.pdf'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'naskah-uji').'.pdf';
        file_put_contents($path, "%PDF-1.4\n%%EOF");

        return new UploadedFile($path, $nama, 'application/pdf', null, true);
    }

    private function dataValid(array $override = []): array
    {
        $kategori = Kategori::create(['nama' => 'Kategori Uji '.uniqid()]);

        return array_merge([
            'nama' => 'Penulis Uji',
            'nama_pena' => 'Pena Uji',
            'email' => 'penulis@example.com',
            'nomor_wa' => '081234567890',
            'alamat' => 'Jl. Uji No. 1',
            'kota' => 'Jakarta',
            'provinsi' => 'DKI Jakarta',
            'judul' => 'Naskah Uji',
            'kategori_id' => $kategori->id,
            'sinopsis' => 'Sinopsis singkat.',
            'naskah' => $this->naskahValid(),
        ], $override);
    }

    public function test_form_bisa_diakses_publik(): void
    {
        $this->get('/ajukan-penerbitan')->assertOk()->assertSee('Ajukan Penerbitan');
    }

    public function test_submit_valid_tersimpan_dengan_benar_dan_status_baru(): void
    {
        Mail::fake();

        $response = $this->post('/ajukan-penerbitan', $this->dataValid());

        $response->assertRedirect(route('ajukan-penerbitan'));
        $response->assertSessionHas('sukses');

        $this->assertDatabaseHas('pengajuan', [
            'nama' => 'Penulis Uji',
            'nama_pena' => 'Pena Uji',
            'email' => 'penulis@example.com',
            'judul' => 'Naskah Uji',
            'status' => StatusPengajuan::Baru->value,
            'karya_id' => null,
        ]);

        $pengajuan = Pengajuan::first();
        Storage::disk('local')->assertExists($pengajuan->naskah_path);
    }

    public function test_field_wajib_harus_diisi(): void
    {
        $response = $this->post('/ajukan-penerbitan', []);

        $response->assertSessionHasErrors(['nama', 'email', 'judul', 'kategori_id', 'naskah']);
    }

    public function test_field_opsional_boleh_kosong(): void
    {
        Mail::fake();

        $data = $this->dataValid();
        unset($data['nama_pena'], $data['nomor_wa'], $data['alamat'], $data['kota'], $data['provinsi'], $data['sinopsis']);

        $response = $this->post('/ajukan-penerbitan', $data);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pengajuan', ['judul' => 'Naskah Uji']);
    }

    public function test_surat_keaslian_opsional_tersimpan_kalau_diunggah(): void
    {
        Mail::fake();

        $suratPath = tempnam(sys_get_temp_dir(), 'surat-uji').'.pdf';
        file_put_contents($suratPath, "%PDF-1.4\n%%EOF");
        $surat = new UploadedFile($suratPath, 'surat.pdf', 'application/pdf', null, true);

        $this->post('/ajukan-penerbitan', $this->dataValid(['surat_keaslian' => $surat]));

        $pengajuan = Pengajuan::first();
        $this->assertNotNull($pengajuan->surat_keaslian_path);
        Storage::disk('local')->assertExists($pengajuan->surat_keaslian_path);
    }

    public function test_surat_keaslian_tidak_diunggah_tetap_berhasil(): void
    {
        Mail::fake();

        $this->post('/ajukan-penerbitan', $this->dataValid());

        $pengajuan = Pengajuan::first();
        $this->assertNull($pengajuan->surat_keaslian_path);
    }

    public function test_naskah_bukan_dokumen_asli_ditolak_meski_ekstensi_pdf(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'naskah-uji').'.pdf';
        file_put_contents($path, 'ini cuma teks biasa, bukan PDF sama sekali');
        $palsu = new UploadedFile($path, 'palsu.pdf', 'application/pdf', null, true);

        $response = $this->post('/ajukan-penerbitan', $this->dataValid(['naskah' => $palsu]));

        $response->assertSessionHasErrors('naskah');
        $this->assertDatabaseCount('pengajuan', 0);
    }

    public function test_naskah_melebihi_10mb_ditolak(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'naskah-uji').'.pdf';
        file_put_contents($path, "%PDF-1.4\n".str_repeat('A', 11 * 1024 * 1024));
        $besar = new UploadedFile($path, 'besar.pdf', 'application/pdf', null, true);

        $response = $this->post('/ajukan-penerbitan', $this->dataValid(['naskah' => $besar]));

        $response->assertSessionHasErrors('naskah');
    }

    public function test_email_konfirmasi_terkirim_ke_pengaju(): void
    {
        Mail::fake();

        $this->post('/ajukan-penerbitan', $this->dataValid(['email' => 'pengaju-uji@example.com']));

        Mail::assertSent(PengajuanDiterimaMail::class, function ($mail) {
            return $mail->hasTo('pengaju-uji@example.com')
                && $mail->pengajuan->judul === 'Naskah Uji';
        });
    }

    public function test_tidak_ada_mass_assignment_ke_status_dan_karya_id(): void
    {
        Mail::fake();

        $data = $this->dataValid([
            'status' => 'disetujui',
            'karya_id' => 999999,
        ]);

        $this->post('/ajukan-penerbitan', $data);

        $pengajuan = Pengajuan::first();
        $this->assertSame(StatusPengajuan::Baru, $pengajuan->status);
        $this->assertNull($pengajuan->karya_id);
    }

    public function test_kategori_tidak_ada_ditolak(): void
    {
        $response = $this->post('/ajukan-penerbitan', $this->dataValid(['kategori_id' => 999999]));

        $response->assertSessionHasErrors('kategori_id');
    }

    public function test_email_tidak_valid_ditolak(): void
    {
        $response = $this->post('/ajukan-penerbitan', $this->dataValid(['email' => 'bukan-email']));

        $response->assertSessionHasErrors('email');
    }

    public function test_route_submit_dibatasi_throttle(): void
    {
        $route = Route::getRoutes()->getByName('ajukan-penerbitan.store');

        $this->assertNotNull($route);
        $this->assertContains('throttle:5,1', $route->middleware());
    }
}
