<?php namespace App\Controllers;

use CodeIgniter\Controller;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Picqer\Barcode\BarcodeGeneratorPNG;

class MediaController extends Controller
{
    public function ping()
    {
        return $this->response->setJSON(['status' => 'media ok']);
    }

    public function selftest()
    {
        return $this->response->setJSON([
            'qr_lib_loaded'      => class_exists(Builder::class) ? 'yes' : 'no',
            'barcode_lib_loaded' => class_exists(BarcodeGeneratorPNG::class) ? 'yes' : 'no',
            'hint'               => 'If no, run composer require endroid/qr-code:^5.0 picqer/php-barcode-generator:^2.4 then composer install --no-dev --optimize-autoloader'
        ]);
    }

    // Offline QR via Endroid v5
    public function qrUid()
    {
        $uid = $this->request->getGet('uid');
        if (!$uid) return $this->response->setStatusCode(400)->setBody('Missing uid');

        $size   = min(600, max(120, (int)($this->request->getGet('size') ?? 220)));
        $margin = min(10,  max(0,   (int)($this->request->getGet('m')    ?? 4)));

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($uid)
            ->errorCorrectionLevel(ErrorCorrectionLevel::LOW())
            ->size($size)
            ->margin($margin)
            ->build();

        return $this->response
            ->setHeader('Content-Type', $result->getMimeType())
            ->setBody($result->getString());
    }

    // Code128 barcode via Picqer (offline)
    public function barcodeUid()
    {
        $uid = $this->request->getGet('uid');
        if (!$uid) return $this->response->setStatusCode(400)->setBody('Missing uid');

        $scale  = max(1, (int)($this->request->getGet('s') ?? 2));   // width scale
        $height = max(30, (int)($this->request->getGet('h') ?? 60)); // px height

        $generator = new BarcodeGeneratorPNG();
        $png = $generator->getBarcode($uid, $generator::TYPE_CODE_128, $scale, $height);

        return $this->response->setHeader('Content-Type', 'image/png')->setBody($png);
    }
}
