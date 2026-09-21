<?php

namespace Tests\Feature;

use App\Exceptions\CoverUploadException;
use App\Services\CoverUploadService;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CoverUploadServiceTest extends TestCase
{
    private CoverUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('storage-karya');
        $this->service = new CoverUploadService;
    }

    public function test_cover_valid_disimpan_sebagai_jpeg_dengan_thumbnail(): void
    {
        $file = UploadedFile::fake()->image('cover.jpg', 800, 1200);

        $hasil = $this->service->unggah($file);

        $this->assertStringEndsWith('.jpg', $hasil['cover_path']);
        $this->assertStringEndsWith('.jpg', $hasil['thumbnail_path']);
        Storage::disk('storage-karya')->assertExists($hasil['cover_path']);
        Storage::disk('storage-karya')->assertExists($hasil['thumbnail_path']);

        $isiCover = Storage::disk('storage-karya')->get($hasil['cover_path']);
        $ukuranCover = getimagesizefromstring($isiCover);
        $this->assertNotFalse($ukuranCover);
        $this->assertLessThanOrEqual(1600, max($ukuranCover[0], $ukuranCover[1]));

        $isiThumb = Storage::disk('storage-karya')->get($hasil['thumbnail_path']);
        $ukuranThumb = getimagesizefromstring($isiThumb);
        $this->assertLessThanOrEqual(400, max($ukuranThumb[0], $ukuranThumb[1]));
    }

    public function test_cover_png_direkonversi_ke_jpeg(): void
    {
        $file = UploadedFile::fake()->image('cover.png', 500, 700);

        $hasil = $this->service->unggah($file);

        $isi = Storage::disk('storage-karya')->get($hasil['cover_path']);
        $ukuran = getimagesizefromstring($isi);
        $this->assertSame('image/jpeg', $ukuran['mime']);
    }

    public function test_dimensi_terlalu_kecil_ditolak(): void
    {
        $this->expectException(CoverUploadException::class);
        $this->expectExceptionMessage('terlalu kecil');

        $this->service->unggah(UploadedFile::fake()->image('kecil.jpg', 100, 100));
    }

    public function test_dimensi_terlalu_besar_ditolak(): void
    {
        $this->expectException(CoverUploadException::class);
        $this->expectExceptionMessage('terlalu besar');

        $this->service->unggah(UploadedFile::fake()->image('lebar.jpg', 6001, 10));
    }

    public function test_ukuran_berkas_melebihi_batas_ditolak(): void
    {
        $this->expectException(CoverUploadException::class);
        $this->expectExceptionMessage('maksimal 8MB');

        $this->service->unggah(UploadedFile::fake()->create('besar.jpg', 9000));
    }

    public function test_format_di_luar_whitelist_ditolak(): void
    {
        $this->expectException(CoverUploadException::class);
        $this->expectExceptionMessage('JPEG, PNG, atau WEBP');

        $this->service->unggah(UploadedFile::fake()->create('naskah.pdf', 100, 'application/pdf'));
    }

    public function test_berkas_menyamar_sebagai_jpeg_tapi_bukan_gambar_asli_ditolak(): void
    {
        /** @var File $file */
        $file = UploadedFile::fake()->create('rusak.jpg', 10);
        $file->mimeType('image/jpeg');

        $this->expectException(CoverUploadException::class);

        $this->service->unggah($file);
    }

    public function test_hapus_menghilangkan_cover_dan_thumbnail_dari_disk(): void
    {
        $hasil = $this->service->unggah(UploadedFile::fake()->image('cover.jpg', 800, 1200));

        $this->service->hapus($hasil['cover_path'], $hasil['thumbnail_path']);

        Storage::disk('storage-karya')->assertMissing($hasil['cover_path']);
        Storage::disk('storage-karya')->assertMissing($hasil['thumbnail_path']);
    }
}
