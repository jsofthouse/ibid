<?php

namespace Tests\Unit;

use App\Enums\StatusIdentitas;
use PHPUnit\Framework\TestCase;

class StatusIdentitasTest extends TestCase
{
    public function test_belum_ber_ibid_hanya_bisa_ke_ibid_diterbitkan(): void
    {
        $this->assertSame([StatusIdentitas::IbidDiterbitkan], StatusIdentitas::BelumBerIbid->allowedTransitions());
    }

    public function test_ibid_diterbitkan_bisa_dipublikasikan_atau_tidak_aktif(): void
    {
        $this->assertTrue(StatusIdentitas::IbidDiterbitkan->bisaBertransisiKe(StatusIdentitas::Dipublikasikan));
        $this->assertTrue(StatusIdentitas::IbidDiterbitkan->bisaBertransisiKe(StatusIdentitas::TidakAktif));
        $this->assertFalse(StatusIdentitas::IbidDiterbitkan->bisaBertransisiKe(StatusIdentitas::Diarsipkan));
    }

    public function test_dipublikasikan_hanya_bisa_ke_tidak_aktif(): void
    {
        $this->assertSame([StatusIdentitas::TidakAktif], StatusIdentitas::Dipublikasikan->allowedTransitions());
    }

    public function test_tidak_aktif_bisa_reaktivasi_atau_diarsipkan(): void
    {
        $tujuan = StatusIdentitas::TidakAktif->allowedTransitions();

        $this->assertContains(StatusIdentitas::Dipublikasikan, $tujuan);
        $this->assertContains(StatusIdentitas::IbidDiterbitkan, $tujuan);
        $this->assertContains(StatusIdentitas::Diarsipkan, $tujuan);
    }

    public function test_diarsipkan_hanya_bisa_kembali_ke_tidak_aktif(): void
    {
        $this->assertSame([StatusIdentitas::TidakAktif], StatusIdentitas::Diarsipkan->allowedTransitions());
    }

    public function test_wajib_alasan_untuk_tidak_aktif_dan_diarsipkan(): void
    {
        $this->assertTrue(StatusIdentitas::Dipublikasikan->wajibAlasan(StatusIdentitas::TidakAktif));
        $this->assertTrue(StatusIdentitas::TidakAktif->wajibAlasan(StatusIdentitas::Diarsipkan));
        $this->assertFalse(StatusIdentitas::TidakAktif->wajibAlasan(StatusIdentitas::Dipublikasikan));
        $this->assertFalse(StatusIdentitas::IbidDiterbitkan->wajibAlasan(StatusIdentitas::Dipublikasikan));
    }
}
