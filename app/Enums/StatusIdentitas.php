<?php

namespace App\Enums;

enum StatusIdentitas: string
{
    case BelumBerIbid = 'belum_ber_ibid';
    case IbidDiterbitkan = 'ibid_diterbitkan';
    case Dipublikasikan = 'dipublikasikan';
    case TidakAktif = 'tidak_aktif';
    case Diarsipkan = 'diarsipkan';

    public function label(): string
    {
        return match ($this) {
            self::BelumBerIbid => 'Belum Ber-IBID',
            self::IbidDiterbitkan => 'IBID Diterbitkan',
            self::Dipublikasikan => 'Dipublikasikan',
            self::TidakAktif => 'Tidak Aktif',
            self::Diarsipkan => 'Diarsipkan',
        };
    }
}
