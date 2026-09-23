<?php

namespace App\Services;

use App\Exceptions\NaskahUploadException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class NaskahUploadService
{
    private const DISK = 'local';

    private const DIREKTORI = 'naskah';

    private const MAKS_UKURAN_BYTE = 10 * 1024 * 1024;

    /**
     * Validasi ulang ukuran & format asli file lewat signature isi berkas
     * (bukan dari nama/ekstensi kiriman user, dan bukan cuma MIME tebakan
     * finfo - database magic finfo/libmagic banyak yang tidak bisa
     * membedakan .docx modern dari ZIP biasa, lihat cekDocx()) lalu simpan
     * dengan nama acak ke storage/app/private/naskah (disk 'local') -
     * privat, hanya diakses lewat route stream admin yang cek permission
     * (FileStreamController, sudah ada sejak Fase 1).
     */
    public function unggah(UploadedFile $file): string
    {
        if ($file->getSize() === false || $file->getSize() > self::MAKS_UKURAN_BYTE) {
            throw new NaskahUploadException('Ukuran berkas maksimal 10MB.');
        }

        $path = $file->getRealPath();

        if ($path === false) {
            throw new NaskahUploadException('Berkas tidak valid.');
        }

        $ekstensi = match (true) {
            $this->cekPdf($path) => 'pdf',
            $this->cekDoc($path) => 'doc',
            $this->cekDocx($path) => 'docx',
            default => null,
        };

        if ($ekstensi === null) {
            throw new NaskahUploadException('Format berkas harus PDF, DOC, atau DOCX.');
        }

        $tujuan = self::DIREKTORI.'/'.Str::random(40).'.'.$ekstensi;

        Storage::disk(self::DISK)->put($tujuan, file_get_contents($path));

        return $tujuan;
    }

    private function cekPdf(string $path): bool
    {
        return str_starts_with((string) file_get_contents($path, false, null, 0, 5), '%PDF-');
    }

    /**
     * Signature OLE2 Compound File Binary Format - dipakai .doc (Word
     * 97-2003) dan format Office lama lain. Tidak membedakan lebih jauh
     * apakah isinya spesifik Word vs Excel/PowerPoint - deteksi itu butuh
     * parsing stream internal yang tidak sepadan dengan tujuan validasi ini
     * (mencegah upload berkas yang jelas bukan dokumen, bukan verifikasi
     * konten mendalam).
     */
    private function cekDoc(string $path): bool
    {
        return file_get_contents($path, false, null, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
    }

    /**
     * .docx adalah paket ZIP OOXML - signature ZIP biasa ("PK..") saja
     * tidak cukup (banyak instalasi finfo/libmagic cuma mendeteksinya
     * sebagai application/zip generik, terverifikasi lokal). Buka
     * sungguhan lewat ZipArchive dan cek ada [Content_Types].xml, berkas
     * wajib yang cuma ada di paket OOXML.
     */
    private function cekDocx(string $path): bool
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            return false;
        }

        $valid = $zip->locateName('[Content_Types].xml') !== false;
        $zip->close();

        return $valid;
    }
}
