<?php

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class Today extends BaseController
{
    public function index()
    {
        $data = [
            'token'   => env('ANDROID_API_TOKEN', ''),
            'baseUrl' => rtrim(site_url(), '/'),
            'uid'     => $this->request->getGet('uid'),
        ];
        return view('admin/tools/today', $data);
    }
}
