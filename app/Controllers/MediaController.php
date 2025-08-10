<?php namespace App\Controllers;

use App\Controllers\BaseController;

class MediaController extends BaseController
{
    public function barcodeUid()
    {
        // Keep your existing barcode method (Composer or previous code)...
        // This file focuses on QR fallback
        return $this->response->setStatusCode(501)->setBody('Use existing barcode method');
    }

    public function qrUid()
    {
        $uid = trim($this->request->getGet('uid') ?? '');
        if ($uid === '') {
            return $this->response->setStatusCode(400)->setBody('Invalid UID');
        }

        // Try composer Endroid first
        try {
            if (class_exists('\\Endroid\\QrCode\\QrCode')) {
                $qr = \Endroid\QrCode\QrCode::create($uid)
                    ->setSize(200)->setMargin(2);
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qr);
                return $this->response->setContentType('image/png')->setBody($result->getString());
            }
        } catch (\Throwable $e) {
            // fallthrough
        }

        // Try local phpqrcode fallback
        $path = APPPATH.'ThirdParty/phpqrcode/qrlib.php';
        if (is_file($path)) {
            require_once $path;
            ob_start();
            qr_png($uid, 200, 2);
            $out = ob_get_clean();
            return $this->response->setContentType('image/png')->setBody($out);
        }

        return $this->response->setStatusCode(500)->setBody('QR generator not available');
    }

    public function ping()
    {
        return 'media ok';
    }
}
