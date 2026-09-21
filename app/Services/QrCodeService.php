<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;

class QrCodeService
{
    private const UKURAN_PIKSEL = 640;

    private const MARGIN_PIKSEL = 16;

    private const LEBAR_LOGO = 120;

    /**
     * Path logo Penerbit Irfani untuk disematkan di tengah QR. Belum ada
     * berkasnya di repo - kalau file ini belum ada, QR tetap digenerate
     * tanpa logo (tetap valid & scannable), tinggal taruh berkasnya di
     * path ini nanti tanpa perlu ubah kode.
     */
    private function pathLogo(): string
    {
        $path = public_path('images/logo-qr.png');

        return file_exists($path) ? $path : '';
    }

    public function svg(string $urlTujuan): ResultInterface
    {
        return $this->builder(new SvgWriter, $urlTujuan)->build();
    }

    public function png(string $urlTujuan): ResultInterface
    {
        return $this->builder(new PngWriter, $urlTujuan)->build();
    }

    private function builder(WriterInterface $writer, string $urlTujuan): Builder
    {
        return new Builder(
            writer: $writer,
            data: $urlTujuan,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: self::UKURAN_PIKSEL,
            margin: self::MARGIN_PIKSEL,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            logoPath: $this->pathLogo(),
            logoResizeToWidth: self::LEBAR_LOGO,
            logoPunchoutBackground: true,
        );
    }
}
