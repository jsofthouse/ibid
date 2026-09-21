<?php

namespace Tests\Unit;

use App\Enums\StatusProduksi;
use PHPUnit\Framework\TestCase;

class StatusProduksiTest extends TestCase
{
    public function test_transisi_maju_satu_langkah_diizinkan(): void
    {
        $this->assertTrue(StatusProduksi::Draft->bisaBertransisiKe(StatusProduksi::Disetujui));
        $this->assertTrue(StatusProduksi::Disetujui->bisaBertransisiKe(StatusProduksi::DalamProses));
        $this->assertTrue(StatusProduksi::DalamProses->bisaBertransisiKe(StatusProduksi::Diterbitkan));
    }

    public function test_lompat_dua_langkah_tidak_diizinkan(): void
    {
        $this->assertFalse(StatusProduksi::Draft->bisaBertransisiKe(StatusProduksi::DalamProses));
        $this->assertFalse(StatusProduksi::Draft->bisaBertransisiKe(StatusProduksi::Diterbitkan));
    }

    public function test_mundur_satu_langkah_diizinkan_secara_struktural(): void
    {
        $this->assertTrue(StatusProduksi::Disetujui->bisaBertransisiKe(StatusProduksi::Draft));
        $this->assertTrue(StatusProduksi::DalamProses->bisaBertransisiKe(StatusProduksi::Disetujui));
        $this->assertTrue(StatusProduksi::Diterbitkan->bisaBertransisiKe(StatusProduksi::DalamProses));
    }

    public function test_diterbitkan_tidak_bisa_dibatalkan(): void
    {
        $this->assertFalse(StatusProduksi::Diterbitkan->bisaBertransisiKe(StatusProduksi::Dibatalkan));
    }

    public function test_dibatalkan_adalah_status_terminal(): void
    {
        $this->assertSame([], StatusProduksi::Dibatalkan->allowedTransitions());
    }

    public function test_wajib_alasan_untuk_mundur_dan_dibatalkan(): void
    {
        $this->assertTrue(StatusProduksi::DalamProses->wajibAlasan(StatusProduksi::Disetujui));
        $this->assertTrue(StatusProduksi::Draft->wajibAlasan(StatusProduksi::Dibatalkan));
        $this->assertFalse(StatusProduksi::Draft->wajibAlasan(StatusProduksi::Disetujui));
    }
}
