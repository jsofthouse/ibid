<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateIbidAction;
use App\Enums\StatusIdentitas;
use App\Exceptions\TransisiStatusException;
use App\Http\Controllers\Controller;
use App\Models\Karya;
use App\Services\AuditService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class KaryaIbidController extends Controller
{
    public function __construct(
        private readonly GenerateIbidAction $generateIbidAction,
        private readonly QrCodeService $qrCodeService,
        private readonly AuditService $auditService,
    ) {}

    public function generate(Karya $karya): RedirectResponse
    {
        try {
            $this->generateIbidAction->execute($karya);
        } catch (TransisiStatusException $e) {
            return redirect()->route('admin.karya.show', $karya)->with('gagal', $e->getMessage());
        }

        return redirect()->route('admin.karya.show', $karya)->with('sukses', 'Nomor IBID berhasil digenerate.');
    }

    public function unduhSvg(Karya $karya): Response
    {
        abort_if($karya->status_identitas === StatusIdentitas::BelumBerIbid, 404);

        $hasil = $this->qrCodeService->svg(route('buku.show', ['ibid' => $karya->ibid_number]));

        $this->auditService->catatModel('unduh_qr_svg', $karya);

        return response($hasil->getString(), 200, ['Content-Type' => $hasil->getMimeType()]);
    }

    public function unduhPng(Karya $karya): Response
    {
        abort_if($karya->status_identitas === StatusIdentitas::BelumBerIbid, 404);

        $hasil = $this->qrCodeService->png(route('buku.show', ['ibid' => $karya->ibid_number]));

        $this->auditService->catatModel('unduh_qr_png', $karya);

        return response($hasil->getString(), 200, ['Content-Type' => $hasil->getMimeType()]);
    }
}
