<?php
declare(strict_types=1);

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class Settings extends BaseController
{
    public function index()
    {
        $row = db_connect()->table('settings')->select('svalue')->where('skey','auto_map_orphans')->get()->getRowArray();
        $val = ($row['svalue'] ?? '1') === '1' ? '1' : '0';
        return $this->response->setJSON(['ok'=>true,'auto_map_orphans'=>$val]);
    }

    public function toggle()
    {
        $enable = $this->request->getPost('enable') ?? $this->request->getGet('enable');
        $val = (string) ((string)$enable === '1' ? '1' : '0');
        db_connect()->table('settings')->ignore(true)->insert([
            'skey'       => 'auto_map_orphans',
            'svalue'     => $val,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        db_connect()->table('settings')->where('skey','auto_map_orphans')->set([
            'svalue'     => $val,
            'updated_at' => date('Y-m-d H:i:s'),
        ])->update();
        return $this->response->setJSON(['ok'=>true,'auto_map_orphans'=>$val]);
    }
}
