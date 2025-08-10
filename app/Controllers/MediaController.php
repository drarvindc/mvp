<?php namespace App\Controllers;

use App\Controllers\BaseController;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;

class MediaController extends BaseController
{
    public function ping()
    {
        return 'media ok';
    }

    // Offline QR via Endroid (Composer)
    public function qrUid()
    {
        $uid = trim($this->request->getGet('uid') ?? '');
        if ($uid === '') {
            return $this->response->setStatusCode(400)->setBody('Invalid UID');
        }
        $size   = min(600, max(120, (int)($this->request->getGet('size') ?? 200)));
        $margin = min(10,  max(0,   (int)($this->request->getGet('m') ?? 2)));

        $qr = QrCode::create($uid)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize($size)
            ->setMargin($margin);

        $writer = new PngWriter();
        $result = $writer->write($qr);

        return $this->response->setContentType('image/png')->setBody($result->getString());
    }

    // Code128 barcode via Picqer (Composer)
    public function barcodeUid()
    {
        $uid = trim($this->request->getGet('uid') ?? '');
        if (!preg_match('/^\d{6}$/',$uid)) {
            return $this->response->setStatusCode(400)->setBody('Invalid UID');
        }
        $scale  = max(1, (int)($this->request->getGet('s') ?? 2));  // width scale
        $height = max(30, (int)($this->request->getGet('h') ?? 60)); // px height

        $gen = new BarcodeGeneratorPNG();
        $png = $gen->getBarcode($uid, $gen::TYPE_CODE_128, $scale, $height);

        // Optional: human-readable text under barcode? (here we don't render text)
        return $this->response->setContentType('image/png')->setBody($png);
    }

    // Quick self-test JSON
    public function selftest()
    {
        $okQr = class_exists('\Endroid\QrCode\QrCode') ? 'yes' : 'no';
        $okBc = class_exists('\Picqer\Barcode\BarcodeGeneratorPNG') ? 'yes' : 'no';
        return $this->response->setJSON([
            'qr_lib_loaded' => $okQr,
            'barcode_lib_loaded' => $okBc,
            'hint' => 'If no, run composer require endroid/qr-code:^5.0 picqer/php-barcode-generator:^2.4 then composer install --no-dev --optimize-autoloader'
        ]);
    }
}
