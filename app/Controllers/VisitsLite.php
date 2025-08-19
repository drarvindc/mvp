<?php
declare(strict_types=1);

namespace App\Controllers;

class VisitsLite extends BaseController
{
    public function index()
    {
        return view('visitslite/index');
    }
}
