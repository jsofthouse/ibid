<?php

namespace Tests\Unit;

use App\Enums\StatusPengajuan;
use PHPUnit\Framework\TestCase;

class StatusPengajuanTest extends TestCase
{
    public function test_transisi_dari_baru(): void
    {
        $this->assertTrue(StatusPengajuan::Baru->bisaBertransisiKe(StatusPengajuan::Diproses));
        $this->assertTrue(StatusPengajuan::Baru->bisaBertransisiKe(StatusPengajuan::Ditolak));
        $this->assertFalse(StatusPengajuan::Baru->bisaBertransisiKe(StatusPengajuan::Disetujui));
    }

    public function test_transisi_dari_diproses(): void
    {
        $this->assertTrue(StatusPengajuan::Diproses->bisaBertransisiKe(StatusPengajuan::Disetujui));
        $this->assertTrue(StatusPengajuan::Diproses->bisaBertransisiKe(StatusPengajuan::Ditolak));
        $this->assertFalse(StatusPengajuan::Diproses->bisaBertransisiKe(StatusPengajuan::Baru));
    }

    public function test_disetujui_dan_ditolak_adalah_status_terminal(): void
    {
        $this->assertSame([], StatusPengajuan::Disetujui->allowedTransitions());
        $this->assertSame([], StatusPengajuan::Ditolak->allowedTransitions());
    }
}
