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
        $qrLib = class_exists(Builder::class) ? 'yes' : 'no';
        $barcodeLib = class_exists(BarcodeGeneratorPNG::class) ? 'yes' : 'no';

        return $this->response->setJSON([
            'qr_lib_loaded'      => $qrLib,
            'barcode_lib_loaded' => $barcodeLib,
            'hint'               => 'If no, run composer require endroid/qr-code:^5.0 picqer/php-barcode-generator:^2.4 then composer install --no-dev --optimize-autoloader'
        ]);
    }

    public function qrUid()
    {
        $uid = $this->request->getGet('uid');
        if (!$uid) {
            return $this->response->setStatusCode(400)->setBody('Missing uid');
        }

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($uid)
            ->errorCorrectionLevel(ErrorCorrectionLevel::LOW())
            ->size(200)
            ->margin(5)
            ->build();

        return $this->response
            ->setHeader('Content-Type', $result->getMimeType())
            ->setBody($result->getString());
    }

    public function barcodeUid()
    {
        $uid = $this->request->getGet('uid');
        if (!$uid) {
            return $this->response->setStatusCode(400)->setBody('Missing uid');
        }

        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($uid, $generator::TYPE_CODE_128);

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setBody($barcode);
    }
}
