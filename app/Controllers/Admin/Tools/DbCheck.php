<?php

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;
use Config\Database;

class DbCheck extends BaseController
{
    public function index()
    {
        $db = Database::connect();
        $data = [];

        try {
            // Check visits index
            $data['visits_index'] = $db->query("SHOW INDEX FROM visits WHERE Key_name='idx_visits_pet_date_seq'")->getResultArray();

            // Check documents columns
            $data['documents_columns'] = $db->query("SHOW COLUMNS FROM documents WHERE Field IN ('pet_id','mime','note')")->getResultArray();

            // Migration table snapshot
            $data['migrations'] = $db->query("SELECT * FROM migrations ORDER BY id DESC LIMIT 20")->getResultArray();

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'ok'    => false,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->response->setJSON([
            'ok'      => true,
            'results' => $data,
        ]);
    }
}
