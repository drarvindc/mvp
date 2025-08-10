<?php namespace App\Controllers;

use App\Controllers\BaseController;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;

class MediaController extends BaseController
{
    public function barcodeUid()
    {
        $uid = trim($this->request->getGet('uid') ?? '');
        if (!preg_match('/^\d{6}$/',$uid)) {
            return $this->response->setStatusCode(400)->setBody('Invalid UID');
        }
        $scale  = max(1, (int)($this->request->getGet('s') ?? 2));
        $height = max(30, (int)($this->request->getGet('h') ?? 60));

        $gen = new BarcodeGeneratorPNG();
        $png = $gen->getBarcode($uid, $gen::TYPE_CODE_128, $scale, $height);

        return $this->response->setContentType('image/png')->setBody($png);
    }

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

    public function ping()
    {
        return 'media ok';
    }
}
