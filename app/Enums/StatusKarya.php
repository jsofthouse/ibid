<?php

namespace App\Enums;

enum StatusKarya: string
{
    case Draft = 'draft';
    case DalamProses = 'dalam_proses';
    case Disetujui = 'disetujui';
    case Diterbitkan = 'diterbitkan';
    case IbidDiterbitkan = 'ibid_diterbitkan';
    case Dipublikasikan = 'dipublikasikan';
    case TidakAktif = 'tidak_aktif';
    case Diarsipkan = 'diarsipkan';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::DalamProses => 'Dalam Proses',
            self::Disetujui => 'Disetujui',
            self::Diterbitkan => 'Diterbitkan',
            self::IbidDiterbitkan => 'IBID Diterbitkan',
            self::Dipublikasikan => 'Dipublikasikan',
            self::TidakAktif => 'Tidak Aktif',
            self::Diarsipkan => 'Diarsipkan',
        };
    }
}
