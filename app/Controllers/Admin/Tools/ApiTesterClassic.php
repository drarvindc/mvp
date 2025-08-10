<?php

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class ApiTesterClassic extends BaseController
{
    public function index()
    {
        $key = $this->request->getGet('key');
        if (!$key || $key !== 'arvindrchauhan1723') {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }
        return view('admin/tools/api_tester_classic');
    }
}
