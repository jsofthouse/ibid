<?php

namespace Tests\Feature;

use App\Services\QrCodeService;
use Tests\TestCase;

class QrCodeServiceTest extends TestCase
{
    public function test_svg_menghasilkan_konten_svg_valid(): void
    {
        $hasil = app(QrCodeService::class)->svg('https://ibid-irfani.id/buku/IRF-2026-000001');

        $this->assertSame('image/svg+xml', $hasil->getMimeType());
        $this->assertStringContainsString('<svg', $hasil->getString());
    }

    public function test_png_menghasilkan_konten_png_valid(): void
    {
        $hasil = app(QrCodeService::class)->png('https://ibid-irfani.id/buku/IRF-2026-000001');

        $this->assertSame('image/png', $hasil->getMimeType());
        $this->assertStringStartsWith("\x89PNG", $hasil->getString());
    }

    public function test_qr_tetap_valid_tanpa_berkas_logo(): void
    {
        // Belum ada berkas logo Penerbit Irfani di repo - pastikan generator
        // tidak error dan tetap menghasilkan QR yang bisa dipakai.
        $hasil = app(QrCodeService::class)->png('https://ibid-irfani.id/buku/IRF-2026-000002');

        $this->assertNotEmpty($hasil->getString());
    }

    public function test_url_yang_dienkode_ikut_config_app_url_bukan_hardcode(): void
    {
        $url = route('buku.show', ['ibid' => 'IRF-2026-000003']);

        $this->assertStringStartsWith(config('app.url'), $url);
    }
}
