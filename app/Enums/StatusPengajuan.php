<?php

namespace App\Enums;

enum StatusPengajuan: string
{
    case Baru = 'baru';
    case Diproses = 'diproses';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Diproses => 'Diproses',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
        };
    }

    /**
     * Daftar status tujuan yang sah dari status saat ini. Disetujui dan
     * Ditolak bersifat terminal - sekali sampai di sana, tidak bisa
     * diubah lagi (lihat guard re-convert di PengajuanService).
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Baru => [self::Diproses, self::Ditolak],
            self::Diproses => [self::Disetujui, self::Ditolak],
            self::Disetujui, self::Ditolak => [],
        };
    }

    public function bisaBertransisiKe(self $ke): bool
    {
        return in_array($ke, $this->allowedTransitions(), true);
    }
}
