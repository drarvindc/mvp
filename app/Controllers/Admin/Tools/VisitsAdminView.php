<?php

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class VisitsAdminView extends BaseController
{
    public function index()
    {
        $data = [
            'token'   => env('ANDROID_API_TOKEN', ''),   // auto-fill, you can override in the form
            'baseUrl' => rtrim(site_url(), '/'),
            // pass through any ?uid=&date= so the view can prefill
            'uid'     => $this->request->getGet('uid'),
            'date'    => $this->request->getGet('date'),
        ];
        return view('admin/tools/visits_admin_view', $data);
    }
}
