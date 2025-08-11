<?php
namespace App\Controllers;

class VisitsLite extends BaseController
{
    public function index()
    {
        $uid  = trim((string) $this->request->getGet('uid'));
        $date = trim((string) ($this->request->getGet('date') ?? date('Y-m-d')));

        return view('visitslite/index', [
            'uid'  => $uid,
            'date' => $date,
        ]);
    }
}
