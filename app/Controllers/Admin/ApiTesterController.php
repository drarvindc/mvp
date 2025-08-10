<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ApiTesterController extends BaseController
{
    public function index()
    {
        return view('admin/api_tester/index');
    }
}
