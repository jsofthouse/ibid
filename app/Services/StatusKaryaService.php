<?php

namespace App\Services;

use App\Actions\GenerateIbidAction;
use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Exceptions\TransisiStatusException;
use App\Models\Karya;
use Illuminate\Support\Facades\DB;

class StatusKaryaService
{
    public function __construct(
        private readonly AuditService $auditService,
        private readonly GenerateIbidAction $generateIbidAction,
    ) {}

    public function ubahStatusProduksi(Karya $karya, StatusProduksi $ke, ?string $alasan = null): Karya
    {
        return DB::transaction(function () use ($karya, $ke, $alasan) {
            /** @var Karya $karya */
            $karya = Karya::query()->whereKey($karya->getKey())->lockForUpdate()->firstOrFail();
            $dari = $karya->status_produksi;

            if ($ke === StatusProduksi::Dibatalkan) {
                throw new TransisiStatusException('Gunakan aksi Batalkan Penerbitan untuk membatalkan karya.');
            }

            if (! $dari->bisaBertransisiKe($ke)) {
                throw new TransisiStatusException("Status produksi tidak bisa berubah dari {$dari->label()} ke {$ke->label()}.");
            }

            if ($ke === StatusProduksi::Draft && $karya->status_identitas !== StatusIdentitas::BelumBerIbid) {
                throw new TransisiStatusException('Karya yang sudah ber-IBID tidak bisa dikembalikan ke status Draft.');
            }

            if ($dari === StatusProduksi::Diterbitkan && $ke === StatusProduksi::DalamProses
                && $karya->status_identitas === StatusIdentitas::Dipublikasikan) {
                throw new TransisiStatusException('Status produksi tidak bisa mundur dari Diterbitkan selama identitas masih Dipublikasikan. Nonaktifkan identitas dulu.');
            }

            if ($dari->wajibAlasan($ke) && ! filled($alasan)) {
                throw new TransisiStatusException('Alasan wajib diisi untuk perubahan status ini.');
            }

            $karya->status_produksi = $ke;

            if ($ke === StatusProduksi::Diterbitkan) {
                $karya->tanggal_diterbitkan = now()->toDateString();
            }

            Karya::withoutEvents(fn () => $karya->save());

            $this->auditService->catatModel(
                aksi: 'ubah_status_produksi',
                model: $karya,
                dataBefore: ['status_produksi' => $dari->value],
                dataAfter: ['status_produksi' => $ke->value],
                keterangan: $alasan,
            );

            // Begitu produksi Disetujui, IBID langsung digenerate supaya QR
            // sudah tersedia sebelum masuk proses editing/layout (PRD §5).
            // Kalau data belum cukup (mis. belum ada penulis), transisi
            // status tetap jalan - superadmin tinggal generate manual nanti
            // begitu datanya lengkap.
            if ($ke === StatusProduksi::Disetujui && $karya->status_identitas === StatusIdentitas::BelumBerIbid) {
                try {
                    $karya = $this->generateIbidAction->execute($karya);
                } catch (TransisiStatusException) {
                    // Sengaja diabaikan - lihat catatan di atas.
                }
            }

            return $karya;
        });
    }

    public function ubahStatusIdentitas(Karya $karya, StatusIdentitas $ke, ?string $alasan = null): Karya
    {
        return DB::transaction(function () use ($karya, $ke, $alasan) {
            /** @var Karya $karya */
            $karya = Karya::query()->whereKey($karya->getKey())->lockForUpdate()->firstOrFail();
            $dari = $karya->status_identitas;

            if ($dari === StatusIdentitas::BelumBerIbid) {
                throw new TransisiStatusException('Gunakan aksi Generate IBID untuk menerbitkan nomor IBID.');
            }

            if (! $dari->bisaBertransisiKe($ke)) {
                throw new TransisiStatusException("Status identitas tidak bisa berubah dari {$dari->label()} ke {$ke->label()}.");
            }

            if ($dari === StatusIdentitas::TidakAktif
                && in_array($ke, [StatusIdentitas::Dipublikasikan, StatusIdentitas::IbidDiterbitkan], true)
                && $karya->status_produksi === StatusProduksi::Dibatalkan) {
                throw new TransisiStatusException('Karya yang penerbitannya dibatalkan tidak bisa diaktifkan kembali.');
            }

            if ($ke === StatusIdentitas::Dipublikasikan && ! $this->dataLengkapUntukPublikasi($karya)) {
                throw new TransisiStatusException('Data karya belum lengkap untuk dipublikasikan (tahun terbit, kota terbit, edisi, bahasa, jumlah halaman, ukuran, dan cover wajib diisi).');
            }

            if ($dari->wajibAlasan($ke) && ! filled($alasan)) {
                throw new TransisiStatusException('Alasan wajib diisi untuk perubahan status ini.');
            }

            $karya->status_identitas = $ke;

            if ($ke === StatusIdentitas::Dipublikasikan && $karya->tanggal_dipublikasikan === null) {
                $karya->tanggal_dipublikasikan = now()->toDateString();
            }

            Karya::withoutEvents(fn () => $karya->save());

            $this->auditService->catatModel(
                aksi: 'ubah_status_identitas',
                model: $karya,
                dataBefore: ['status_identitas' => $dari->value],
                dataAfter: ['status_identitas' => $ke->value],
                keterangan: $alasan,
            );

            return $karya;
        });
    }

    public function batalkanPenerbitan(Karya $karya, string $alasan, ?string $konfirmasiNomorIbid = null): Karya
    {
        return DB::transaction(function () use ($karya, $alasan, $konfirmasiNomorIbid) {
            /** @var Karya $karya */
            $karya = Karya::query()->whereKey($karya->getKey())->lockForUpdate()->firstOrFail();

            $produksiBolehDibatalkan = [StatusProduksi::Draft, StatusProduksi::Disetujui, StatusProduksi::DalamProses];

            if (! in_array($karya->status_produksi, $produksiBolehDibatalkan, true)) {
                throw new TransisiStatusException('Karya yang sudah Diterbitkan tidak bisa dibatalkan lewat aksi ini - nonaktifkan identitasnya sebagai gantinya.');
            }

            if (! filled($alasan)) {
                throw new TransisiStatusException('Alasan pembatalan wajib diisi.');
            }

            $sudahBerIbid = $karya->status_identitas !== StatusIdentitas::BelumBerIbid;

            if ($sudahBerIbid && $konfirmasiNomorIbid !== $karya->ibid_number) {
                throw new TransisiStatusException('Konfirmasi nomor IBID tidak cocok.');
            }

            $statusProduksiSebelum = $karya->status_produksi;
            $statusIdentitasSebelum = $karya->status_identitas;

            $karya->status_produksi = StatusProduksi::Dibatalkan;

            if ($sudahBerIbid) {
                $karya->status_identitas = StatusIdentitas::TidakAktif;
            }

            Karya::withoutEvents(fn () => $karya->save());

            $this->auditService->catatModel(
                aksi: 'batalkan_penerbitan',
                model: $karya,
                dataBefore: [
                    'status_produksi' => $statusProduksiSebelum->value,
                    'status_identitas' => $statusIdentitasSebelum->value,
                ],
                dataAfter: [
                    'status_produksi' => $karya->status_produksi->value,
                    'status_identitas' => $karya->status_identitas->value,
                ],
                keterangan: $alasan,
            );

            return $karya;
        });
    }

    private function dataLengkapUntukPublikasi(Karya $karya): bool
    {
        return $karya->status_produksi === StatusProduksi::Diterbitkan
            && filled($karya->tahun_terbit)
            && filled($karya->kota_terbit)
            && filled($karya->edisi)
            && filled($karya->bahasa)
            && filled($karya->jumlah_halaman)
            && filled($karya->ukuran)
            && filled($karya->cover_path);
    }
}
