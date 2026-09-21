<?php

namespace App\Enums;

enum StatusProduksi: string
{
    case Draft = 'draft';
    case Disetujui = 'disetujui';
    case DalamProses = 'dalam_proses';
    case Diterbitkan = 'diterbitkan';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Disetujui => 'Disetujui',
            self::DalamProses => 'Dalam Proses',
            self::Diterbitkan => 'Diterbitkan',
            self::Dibatalkan => 'Dibatalkan',
        };
    }
}
