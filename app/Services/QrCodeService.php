<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    public function dataUri(string $content, int $size = 300): string
    {
        $qrCode = new QrCode(
            data: $content,
            size: $size,
            margin: 10,
        );

        $writer = new SvgWriter;

        return $writer->write($qrCode)->getDataUri();
    }
}
