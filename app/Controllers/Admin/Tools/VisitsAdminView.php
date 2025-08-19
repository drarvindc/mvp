<?php

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class VisitsAdminView extends BaseController
{
    public function index()
    {
        // Pass env values to the view; the view will call the API via fetch()
        $data = [
            'token'   => env('ANDROID_API_TOKEN', ''),  // you can override in the form
            'baseUrl' => rtrim(site_url(), '/'),
        ];
        return view('admin/tools/visits_admin_view', $data);
    }
}
