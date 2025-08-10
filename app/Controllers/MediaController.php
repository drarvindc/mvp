<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Barcode128;

class MediaController extends BaseController
{
    public function barcodeUid()
    {
        $uid = trim($this->request->getGet('uid') ?? '');
        if (!preg_match('/^\d{6}$/',$uid)) {
            return $this->response->setStatusCode(400)->setBody('Invalid UID');
        }
        $height = intval($this->request->getGet('h') ?? 60);
        $scale  = intval($this->request->getGet('s') ?? 2);
        $gen = new Barcode128();
        $gen->renderPng($uid, $height, $scale);
        return;
    }

    public function qrUid()
    {
        $uid = trim($this->request->getGet('uid') ?? '');
        if ($uid === '') {
            return $this->response->setStatusCode(400)->setBody('Invalid UID');
        }
        $size = intval($this->request->getGet('size') ?? 6);
        $margin = intval($this->request->getGet('m') ?? 1);
        require_once APPPATH.'ThirdParty/phpqrcode/qrlib.php';
        // Using fallback QR generator; replace with local library for offline use.
        QRcode_png($uid, $size, $margin);
        return;
    }
}
