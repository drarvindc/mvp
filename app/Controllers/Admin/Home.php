<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Home extends BaseController
{
    public function index()
    {
        // Redirect to admin/tools as the default landing page
        return redirect()->to(site_url('admin/tools'));
    }
}
