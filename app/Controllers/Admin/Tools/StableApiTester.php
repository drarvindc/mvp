<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class StableApiTester extends BaseController
{
    public function index()
    {
        $key = $this->request->getGet('key');
        if ($key !== 'arvindrchauhan1723') {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }
        $token = env('ANDROID_API_TOKEN') ?: '';
        return view('admin/tools/stable_api_tester', ['token'=>$token]);
    }
}
