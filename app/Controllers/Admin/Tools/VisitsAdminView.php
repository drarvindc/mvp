<?php

namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;
use CodeIgniter\Database\Exceptions\DataException;

class VisitsAdminView extends BaseController
{
    public function index()
    {
        $uid  = $this->request->getGet('uid');
        $date = $this->request->getGet('date');

        if (empty($uid) || empty($date)) {
            return view('admin/tools/visits_admin_view', [
                'error' => 'Please supply both uid and date (YYYY-MM-DD)',
                'visit' => null,
                'documents' => [],
            ]);
        }

        $db = db_connect();

        $visit = $db->table('visits')
            ->where('patient_unique_id', $uid)
            ->where('visit_date', $date)
            ->get()
            ->getRowArray();

        $documents = [];
        if ($visit) {
            $documents = $db->table('documents')
                ->where('visit_id', $visit['id'])
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('admin/tools/visits_admin_view', [
            'error'     => $visit ? null : 'No visit found',
            'visit'     => $visit,
            'documents' => $documents,
        ]);
    }
}
