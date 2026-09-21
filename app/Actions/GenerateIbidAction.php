<?php

namespace App\Actions;

use App\Enums\PeranOrang;
use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Exceptions\TransisiStatusException;
use App\Models\Karya;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class GenerateIbidAction
{
    private const PRODUKSI_MEMENUHI_SYARAT = [
        StatusProduksi::Disetujui,
        StatusProduksi::DalamProses,
        StatusProduksi::Diterbitkan,
    ];

    public function __construct(private readonly AuditService $auditService) {}

    public function execute(Karya $karya): Karya
    {
        return DB::transaction(function () use ($karya) {
            /** @var Karya $karya */
            $karya = Karya::query()->whereKey($karya->getKey())->lockForUpdate()->firstOrFail();

            if ($karya->status_identitas !== StatusIdentitas::BelumBerIbid) {
                throw new TransisiStatusException('Karya ini sudah memiliki nomor IBID.');
            }

            if (! in_array($karya->status_produksi, self::PRODUKSI_MEMENUHI_SYARAT, true)) {
                throw new TransisiStatusException('Status produksi karya minimal harus Disetujui sebelum generate IBID.');
            }

            if (blank($karya->judul) || blank($karya->kategori_id)) {
                throw new TransisiStatusException('Judul dan kategori wajib diisi sebelum generate IBID.');
            }

            $jumlahPenulis = $karya->daftarOrang()->wherePivot('role', PeranOrang::Penulis->value)->count();

            if ($jumlahPenulis < 1) {
                throw new TransisiStatusException('Karya wajib memiliki minimal satu penulis sebelum generate IBID.');
            }

            $ibidNumber = $this->nomorIbidBerikutnya();

            $karya->forceFill([
                'ibid_number' => $ibidNumber,
                'status_identitas' => StatusIdentitas::IbidDiterbitkan,
                'tanggal_ibid' => now()->toDateString(),
            ]);
            Karya::withoutEvents(fn () => $karya->save());

            $this->auditService->catatModel(
                aksi: 'generate_ibid',
                model: $karya,
                dataBefore: ['ibid_number' => null, 'status_identitas' => StatusIdentitas::BelumBerIbid->value],
                dataAfter: ['ibid_number' => $ibidNumber, 'status_identitas' => StatusIdentitas::IbidDiterbitkan->value],
            );

            return $karya;
        });
    }

    /**
     * Nomor naik berurutan global (tanpa reset tahunan) lewat tabel counter
     * 1 baris yang di-lock di dalam transaction yang sama dengan update
     * karya - jadi kalau ada race condition, salah satu transaksi menunggu
     * baris counter selesai di-lock, bukan baca nilai basi. Unique index
     * di kolom ibid_number (migration Fase 1) jadi jaring pengaman kedua.
     */
    private function nomorIbidBerikutnya(): string
    {
        $counter = DB::table('ibid_counter')->lockForUpdate()->first();
        $nomorBaru = $counter->nomor_terakhir + 1;

        DB::table('ibid_counter')->where('id', $counter->id)->update([
            'nomor_terakhir' => $nomorBaru,
            'updated_at' => now(),
        ]);

        return sprintf('IRF-%d-%06d', now()->year, $nomorBaru);
    }
}
