<?php

namespace Tests\Unit;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPUnit\Framework\TestCase;

class WorkSheetQrImageTest extends TestCase
{
    public function test_a_worksheet_url_can_be_rendered_as_a_jpeg_qr_image(): void
    {
        $qrCode = new QrCode(
            'https://example.test/ppat/lembar-kerja/LK-261005-ABCDE',
            new Encoding('UTF-8'),
            ErrorCorrectionLevel::Medium,
            480,
            16,
        );
        $png = (new PngWriter())->write($qrCode)->getString();
        $image = imagecreatefromstring($png);

        $this->assertInstanceOf(\GdImage::class, $image);

        ob_start();
        imagejpeg($image, null, 100);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        $this->assertStringStartsWith("\xFF\xD8\xFF", $jpeg);
    }
}
