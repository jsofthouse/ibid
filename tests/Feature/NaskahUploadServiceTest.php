<?php

namespace Tests\Feature;

use App\Exceptions\NaskahUploadException;
use App\Services\NaskahUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class NaskahUploadServiceTest extends TestCase
{
    private NaskahUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->service = new NaskahUploadService;
    }

    private function fileDenganIsi(string $namaAsli, string $isi, string $mimeKlien): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'naskah-uji');
        file_put_contents($path, $isi);

        return new UploadedFile($path, $namaAsli, $mimeKlien, null, true);
    }

    private function fileDocxAsli(string $namaAsli = 'naskah.docx'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'naskah-uji').'.docx';
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<Types/>');
        $zip->addFromString('word/document.xml', '<document/>');
        $zip->close();

        return new UploadedFile($path, $namaAsli, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);
    }

    public function test_pdf_asli_diterima_dan_disimpan_privat(): void
    {
        $file = $this->fileDenganIsi('naskah.pdf', "%PDF-1.4\n%%EOF", 'application/pdf');

        $path = $this->service->unggah($file);

        $this->assertStringStartsWith('naskah/', $path);
        $this->assertStringEndsWith('.pdf', $path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_doc_asli_diterima(): void
    {
        $file = $this->fileDenganIsi('naskah.doc', "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1".str_repeat("\0", 200), 'application/msword');

        $path = $this->service->unggah($file);

        $this->assertStringEndsWith('.doc', $path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_docx_asli_diterima(): void
    {
        $path = $this->service->unggah($this->fileDocxAsli());

        $this->assertStringEndsWith('.docx', $path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_nama_file_disimpan_acak_bukan_nama_asli(): void
    {
        $path = $this->service->unggah($this->fileDenganIsi('rencana-rahasia-penulis.pdf', "%PDF-1.4\n%%EOF", 'application/pdf'));

        $this->assertStringNotContainsString('rencana-rahasia-penulis', $path);
    }

    public function test_teks_biasa_menyamar_ekstensi_pdf_ditolak(): void
    {
        $this->expectException(NaskahUploadException::class);
        $this->expectExceptionMessage('PDF, DOC, atau DOCX');

        $this->service->unggah($this->fileDenganIsi('palsu.pdf', 'ini cuma teks biasa, bukan PDF sama sekali', 'application/pdf'));
    }

    public function test_zip_biasa_menyamar_ekstensi_docx_ditolak(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'naskah-uji').'.docx';
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('random.txt', 'isi random, bukan paket OOXML');
        $zip->close();
        $file = new UploadedFile($path, 'bukan-docx-asli.docx', 'application/zip', null, true);

        $this->expectException(NaskahUploadException::class);
        $this->expectExceptionMessage('PDF, DOC, atau DOCX');

        $this->service->unggah($file);
    }

    public function test_gambar_menyamar_ekstensi_pdf_ditolak(): void
    {
        $this->expectException(NaskahUploadException::class);

        $this->service->unggah(UploadedFile::fake()->image('bukan-naskah.pdf', 100, 100));
    }

    public function test_ukuran_melebihi_10mb_ditolak(): void
    {
        $this->expectException(NaskahUploadException::class);
        $this->expectExceptionMessage('maksimal 10MB');

        $file = $this->fileDenganIsi('besar.pdf', "%PDF-1.4\n".str_repeat('A', 11 * 1024 * 1024), 'application/pdf');
        $this->service->unggah($file);
    }
}
