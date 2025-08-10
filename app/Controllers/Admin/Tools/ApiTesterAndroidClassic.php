<?php namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class ApiTesterAndroidClassic extends BaseController
{
    public function index()
    {
        // simple key gate to mirror your tools style
        $key = $this->request->getGet('key');
        if (!$key || $key !== 'arvindrchauhan1723') {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }
        return view('admin/tools/api_tester_android_classic');
    }
}
