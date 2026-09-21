<?php

namespace App\Services;

use App\Exceptions\CoverUploadException;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CoverUploadService
{
    private const DISK = 'storage-karya';

    private const DIREKTORI_COVER = 'cover';

    private const DIREKTORI_THUMBNAIL = 'cover/thumb';

    private const MAKS_UKURAN_BYTE = 8 * 1024 * 1024;

    private const MAKS_DIMENSI_ASLI = 6000;

    private const MIN_DIMENSI = 200;

    private const MAKS_SISI_COVER = 1600;

    private const MAKS_SISI_THUMBNAIL = 400;

    private const KUALITAS_JPEG = 82;

    private const MIME_DIIZINKAN = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Validasi, re-encode (buang EXIF & metadata lain), dan simpan cover +
     * thumbnail ke disk publik 'storage-karya'. Melempar CoverUploadException
     * kalau berkas bukan gambar valid atau di luar batas ukuran/dimensi.
     *
     * @return array{cover_path: string, thumbnail_path: string}
     */
    public function unggah(UploadedFile $file): array
    {
        if ($file->getSize() === false || $file->getSize() > self::MAKS_UKURAN_BYTE) {
            throw new CoverUploadException('Ukuran berkas cover maksimal 8MB.');
        }

        if (! in_array($file->getMimeType(), self::MIME_DIIZINKAN, true)) {
            throw new CoverUploadException('Format cover harus JPEG, PNG, atau WEBP.');
        }

        // getimagesize() hanya membaca header gambar, bukan mendekode seluruh
        // pixel - aman dipakai untuk cek dimensi sebelum imagecreatefromXXX()
        // supaya tidak kena "decompression bomb" (file kecil, dimensi raksasa).
        $info = @getimagesize($file->getRealPath());

        if ($info === false) {
            throw new CoverUploadException('Berkas bukan gambar yang valid.');
        }

        [$lebarAsli, $tinggiAsli, $tipe] = $info;
        $pembuatGambar = $this->pembuatGambarUntukTipe($tipe);

        if ($pembuatGambar === null) {
            throw new CoverUploadException('Format cover harus JPEG, PNG, atau WEBP.');
        }

        if ($lebarAsli > self::MAKS_DIMENSI_ASLI || $tinggiAsli > self::MAKS_DIMENSI_ASLI) {
            throw new CoverUploadException('Dimensi gambar terlalu besar (maksimal '.self::MAKS_DIMENSI_ASLI.'x'.self::MAKS_DIMENSI_ASLI.' piksel).');
        }

        if ($lebarAsli < self::MIN_DIMENSI || $tinggiAsli < self::MIN_DIMENSI) {
            throw new CoverUploadException('Dimensi gambar terlalu kecil (minimal '.self::MIN_DIMENSI.'x'.self::MIN_DIMENSI.' piksel).');
        }

        $sumber = @$pembuatGambar($file->getRealPath());

        if ($sumber === false) {
            throw new CoverUploadException('Berkas gambar rusak atau tidak bisa dibaca.');
        }

        $namaAcak = Str::random(40);
        $coverPath = self::DIREKTORI_COVER.'/'.$namaAcak.'.jpg';
        $thumbnailPath = self::DIREKTORI_THUMBNAIL.'/'.$namaAcak.'.jpg';

        Storage::disk(self::DISK)->put(
            $coverPath,
            $this->encodeJpeg($sumber, $lebarAsli, $tinggiAsli, self::MAKS_SISI_COVER),
        );

        Storage::disk(self::DISK)->put(
            $thumbnailPath,
            $this->encodeJpeg($sumber, $lebarAsli, $tinggiAsli, self::MAKS_SISI_THUMBNAIL),
        );

        imagedestroy($sumber);

        return [
            'cover_path' => $coverPath,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    public function hapus(?string $coverPath, ?string $thumbnailPath = null): void
    {
        $disk = Storage::disk(self::DISK);

        if ($coverPath) {
            $disk->delete($coverPath);
        }

        if ($thumbnailPath) {
            $disk->delete($thumbnailPath);
        }
    }

    /**
     * @return (callable(string): (GdImage|false))|null
     */
    private function pembuatGambarUntukTipe(int $tipeExif): ?callable
    {
        return match ($tipeExif) {
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG => 'imagecreatefrompng',
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? 'imagecreatefromwebp' : null,
            default => null,
        };
    }

    private function encodeJpeg(GdImage $sumber, int $lebarAsli, int $tinggiAsli, int $maksSisi): string
    {
        [$lebarBaru, $tinggiBaru] = $this->hitungDimensi($lebarAsli, $tinggiAsli, $maksSisi);

        $tujuan = imagecreatetruecolor($lebarBaru, $tinggiBaru);
        $putih = imagecolorallocate($tujuan, 255, 255, 255);
        imagefill($tujuan, 0, 0, $putih);

        imagecopyresampled($tujuan, $sumber, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebarAsli, $tinggiAsli);

        ob_start();
        imagejpeg($tujuan, null, self::KUALITAS_JPEG);
        $isi = ob_get_clean();

        imagedestroy($tujuan);

        return $isi;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function hitungDimensi(int $lebar, int $tinggi, int $maksSisi): array
    {
        $sisiTerpanjang = max($lebar, $tinggi);

        if ($sisiTerpanjang <= $maksSisi) {
            return [$lebar, $tinggi];
        }

        $skala = $maksSisi / $sisiTerpanjang;

        return [
            max(1, (int) round($lebar * $skala)),
            max(1, (int) round($tinggi * $skala)),
        ];
    }
}
