<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusIdentitas;
use App\Enums\StatusProduksi;
use App\Exceptions\TransisiStatusException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BatalkanPenerbitanRequest;
use App\Http\Requests\Admin\UbahStatusIdentitasRequest;
use App\Http\Requests\Admin\UbahStatusProduksiRequest;
use App\Models\Karya;
use App\Services\StatusKaryaService;
use Illuminate\Http\RedirectResponse;

class KaryaStatusController extends Controller
{
    public function __construct(private readonly StatusKaryaService $statusKaryaService) {}

    public function ubahProduksi(UbahStatusProduksiRequest $request, Karya $karya): RedirectResponse
    {
        $sudahBerIbidSebelumnya = $karya->status_identitas !== StatusIdentitas::BelumBerIbid;

        try {
            $hasil = $this->statusKaryaService->ubahStatusProduksi(
                $karya,
                StatusProduksi::from($request->validated('status_produksi')),
                $request->validated('alasan'),
            );
        } catch (TransisiStatusException $e) {
            return redirect()->route('admin.karya.show', $karya)->with('gagal', $e->getMessage());
        }

        $pesan = 'Status produksi berhasil diubah.';

        if (! $sudahBerIbidSebelumnya && $hasil->ibid_number) {
            $pesan .= ' Nomor IBID otomatis digenerate: '.$hasil->ibid_number.'.';
        }

        return redirect()->route('admin.karya.show', $karya)->with('sukses', $pesan);
    }

    public function ubahIdentitas(UbahStatusIdentitasRequest $request, Karya $karya): RedirectResponse
    {
        try {
            $this->statusKaryaService->ubahStatusIdentitas(
                $karya,
                StatusIdentitas::from($request->validated('status_identitas')),
                $request->validated('alasan'),
            );
        } catch (TransisiStatusException $e) {
            return redirect()->route('admin.karya.show', $karya)->with('gagal', $e->getMessage());
        }

        return redirect()->route('admin.karya.show', $karya)->with('sukses', 'Status identitas berhasil diubah.');
    }

    public function batalkan(BatalkanPenerbitanRequest $request, Karya $karya): RedirectResponse
    {
        try {
            $this->statusKaryaService->batalkanPenerbitan(
                $karya,
                $request->validated('alasan'),
                $request->validated('konfirmasi_nomor_ibid'),
            );
        } catch (TransisiStatusException $e) {
            return redirect()->route('admin.karya.show', $karya)->with('gagal', $e->getMessage());
        }

        return redirect()->route('admin.karya.show', $karya)->with('sukses', 'Penerbitan karya berhasil dibatalkan.');
    }
}
