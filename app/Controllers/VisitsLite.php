<?php namespace App\Controllers;

use App\Controllers\BaseController;

class VisitsLite extends BaseController
{
    public function index()
    {
        return view('visitslite/index');
    }
}
