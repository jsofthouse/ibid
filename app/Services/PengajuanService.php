<?php

namespace App\Services;

use App\Enums\PeranOrang;
use App\Enums\StatusPengajuan;
use App\Enums\StatusProduksi;
use App\Exceptions\TransisiStatusException;
use App\Models\Karya;
use App\Models\Orang;
use App\Models\Pengajuan;
use Illuminate\Support\Facades\DB;

class PengajuanService
{
    public function __construct(private readonly AuditService $auditService) {}

    public function ubahStatus(Pengajuan $pengajuan, StatusPengajuan $ke, ?string $catatanAdmin = null): Pengajuan
    {
        return DB::transaction(function () use ($pengajuan, $ke, $catatanAdmin) {
            /** @var Pengajuan $pengajuan */
            $pengajuan = Pengajuan::query()->whereKey($pengajuan->getKey())->lockForUpdate()->firstOrFail();

            if ($pengajuan->karya_id !== null) {
                throw new TransisiStatusException('Pengajuan ini sudah dikonversi menjadi karya, tidak bisa diproses ulang.');
            }

            $dari = $pengajuan->status;

            if (! $dari->bisaBertransisiKe($ke)) {
                throw new TransisiStatusException("Status pengajuan tidak bisa berubah dari {$dari->label()} ke {$ke->label()}.");
            }

            if ($ke === StatusPengajuan::Ditolak && ! filled($catatanAdmin)) {
                throw new TransisiStatusException('Catatan admin wajib diisi untuk menolak pengajuan.');
            }

            $pengajuan->status = $ke;

            if (filled($catatanAdmin)) {
                $pengajuan->catatan_admin = $catatanAdmin;
            }

            $karya = null;

            if ($ke === StatusPengajuan::Disetujui) {
                $karya = $this->buatKaryaDariPengajuan($pengajuan);
                $pengajuan->karya_id = $karya->id;
            }

            Pengajuan::withoutEvents(fn () => $pengajuan->save());

            $aksi = match ($ke) {
                StatusPengajuan::Diproses => 'proses_pengajuan',
                StatusPengajuan::Disetujui => 'setujui_pengajuan',
                StatusPengajuan::Ditolak => 'tolak_pengajuan',
                StatusPengajuan::Baru => 'ubah_status_pengajuan',
            };

            $this->auditService->catatModel(
                aksi: $aksi,
                model: $pengajuan,
                dataBefore: ['status' => $dari->value],
                dataAfter: ['status' => $ke->value],
                keterangan: $catatanAdmin,
            );

            if ($karya) {
                $this->auditService->catatModel(
                    aksi: 'convert_pengajuan_ke_karya',
                    model: $pengajuan,
                    dataBefore: null,
                    dataAfter: ['karya_id' => $karya->id],
                );
            }

            return $pengajuan;
        });
    }

    /**
     * Prefill Karya baru dari data Pengajuan: judul, kategori, sinopsis, dan
     * satu penulis (dibuat sebagai record Orang baru dari identitas pengaju).
     * status_produksi = Disetujui, status_identitas tetap default Belum Ber-IBID.
     */
    private function buatKaryaDariPengajuan(Pengajuan $pengajuan): Karya
    {
        $karya = new Karya([
            'judul' => $pengajuan->judul,
            'kategori_id' => $pengajuan->kategori_id,
            'sinopsis' => $pengajuan->sinopsis,
        ]);
        $karya->status_produksi = StatusProduksi::Disetujui;
        $karya->save();

        $penulis = Orang::create([
            'nama' => $pengajuan->nama,
            'nama_pena' => $pengajuan->nama_pena,
            'email' => $pengajuan->email,
            'nomor_wa' => $pengajuan->nomor_wa,
            'alamat' => $pengajuan->alamat,
            'kota' => $pengajuan->kota,
            'provinsi' => $pengajuan->provinsi,
        ]);
        $penulis->daftarRole()->firstOrCreate(['role' => PeranOrang::Penulis->value]);
        $karya->daftarOrang()->attach($penulis->id, ['role' => PeranOrang::Penulis->value]);

        return $karya;
    }
}
