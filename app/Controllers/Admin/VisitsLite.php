<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class VisitsLite extends Controller
{
    public function index()
    {
        // No auth filter; purely a thin view loader.
        // Accepts ?uid=...&date=YYYY-MM-DD (date is optional; API defaults to today)
        $uid  = trim((string) ($this->request->getGet('uid') ?? ''));
        $date = trim((string) ($this->request->getGet('date') ?? ''));

        return view('admin/visits_lite', [
            'uid'  => $uid,
            'date' => $date,
            'base' => rtrim(site_url(), '/'),
        ]);
    }
}
