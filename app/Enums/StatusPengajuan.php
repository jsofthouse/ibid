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
}
