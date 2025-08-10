<?php
namespace App\Controllers;

class VisitsLite extends BaseController
{
    public function index()
    {
        // Just serve the public view; all data is fetched via /api/visit/today?all=1
        return view('visitslite/index');
    }
}
