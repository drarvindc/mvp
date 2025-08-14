<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class Home extends BaseController
{
    public function index()
    {
        return view('admin/tools/index');
    }
}
