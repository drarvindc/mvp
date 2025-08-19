<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class UploadTesterMulti extends BaseController
{
    public function index()
    {
        // Very simple view render (no server state)
        return view('admin/tools/upload_tester_multi');
    }
}
