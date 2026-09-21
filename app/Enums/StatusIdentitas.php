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

    /**
     * Daftar status_identitas tujuan yang sah secara struktural.
     * BelumBerIbid -> IbidDiterbitkan cuma sah lewat aksi Generate IBID
     * (lihat StatusKaryaService::ubahStatusIdentitas, yang menolaknya secara
     * eksplisit) - bukan lewat form ubah status biasa.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::BelumBerIbid => [self::IbidDiterbitkan],
            self::IbidDiterbitkan => [self::Dipublikasikan, self::TidakAktif],
            self::Dipublikasikan => [self::TidakAktif],
            self::TidakAktif => [self::Dipublikasikan, self::IbidDiterbitkan, self::Diarsipkan],
            self::Diarsipkan => [self::TidakAktif],
        };
    }

    public function bisaBertransisiKe(self $ke): bool
    {
        return in_array($ke, $this->allowedTransitions(), true);
    }

    /**
     * Alasan wajib kalau tujuannya Tidak Aktif atau Diarsipkan.
     */
    public function wajibAlasan(self $ke): bool
    {
        return in_array($ke, [self::TidakAktif, self::Diarsipkan], true);
    }
}
