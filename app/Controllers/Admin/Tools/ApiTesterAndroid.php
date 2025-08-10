<?php

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class ApiTesterAndroid extends BaseController
{
    public function index()
    {
        // simple key gate to keep parity with your other tool pages
        $key = $this->request->getGet('key');
        if (!$key || $key !== 'arvindrchauhan1723') {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }
        return view('admin/tools/api_tester_android', [
            'key' => $key,
            'siteUpload' => site_url('api/visit/upload'),
            'siteOpen'   => site_url('api/visit/open'),
            'siteToday'  => site_url('api/visit/today'),
        ]);
    }
}
