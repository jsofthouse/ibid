<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPengajuan;
use App\Exceptions\TransisiStatusException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UbahStatusPengajuanRequest;
use App\Models\Pengajuan;
use App\Services\PengajuanService;
use Illuminate\Http\RedirectResponse;

class PengajuanStatusController extends Controller
{
    public function __construct(private readonly PengajuanService $pengajuanService) {}

    public function ubah(UbahStatusPengajuanRequest $request, Pengajuan $pengajuan): RedirectResponse
    {
        try {
            $hasil = $this->pengajuanService->ubahStatus(
                $pengajuan,
                StatusPengajuan::from($request->validated('status')),
                $request->validated('catatan_admin'),
            );
        } catch (TransisiStatusException $e) {
            return redirect()->route('admin.pengajuan.show', $pengajuan)->with('gagal', $e->getMessage());
        }

        $pesan = 'Status pengajuan berhasil diubah.';

        if ($hasil->karya_id) {
            $pesan = 'Pengajuan disetujui dan berhasil dikonversi menjadi karya baru.';
        }

        return redirect()->route('admin.pengajuan.show', $pengajuan)->with('sukses', $pesan);
    }
}
