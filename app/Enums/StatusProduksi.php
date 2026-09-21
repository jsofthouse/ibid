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

    /**
     * Daftar status_produksi tujuan yang sah dari status saat ini.
     * Termasuk Dibatalkan secara struktural, tapi aksi itu hanya boleh
     * dieksekusi lewat alur "Batalkan Penerbitan" (lihat StatusKaryaService),
     * bukan lewat ubah status produksi biasa.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Disetujui, self::Dibatalkan],
            self::Disetujui => [self::DalamProses, self::Draft, self::Dibatalkan],
            self::DalamProses => [self::Diterbitkan, self::Disetujui, self::Dibatalkan],
            self::Diterbitkan => [self::DalamProses],
            self::Dibatalkan => [],
        };
    }

    public function bisaBertransisiKe(self $ke): bool
    {
        return in_array($ke, $this->allowedTransitions(), true);
    }

    /**
     * Alasan wajib diisi kalau tujuannya mundur satu langkah atau Dibatalkan.
     */
    public function wajibAlasan(self $ke): bool
    {
        return $ke === self::Dibatalkan || $ke->urutan() < $this->urutan();
    }

    /**
     * Urutan linear Draft(0) -> Diterbitkan(3). Dibatalkan bukan bagian
     * dari urutan maju/mundur, jadi diberi nilai besar supaya tidak pernah
     * dianggap "mundur" oleh wajibAlasan().
     */
    private function urutan(): int
    {
        return match ($this) {
            self::Draft => 0,
            self::Disetujui => 1,
            self::DalamProses => 2,
            self::Diterbitkan => 3,
            self::Dibatalkan => 99,
        };
    }
}
