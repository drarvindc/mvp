<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Visits extends BaseController
{
    public function index()
    {
        $uid  = trim((string) $this->request->getGet('uid'));
        $date = trim((string) $this->request->getGet('date')) ?: date('Y-m-d');
        $seq  = (int) ($this->request->getGet('seq') ?? 0);

        $data = [
            'uid'    => $uid,
            'date'   => $date,
            'patient'=> null,
            'visits' => [],
            'active' => $seq,
        ];

        if ($uid === '') {
            return view('admin/visits_dashboard', $data);
        }

        $patient = model('PatientModel')->where('uid', $uid)->first();
        if (!$patient) {
            $data['error'] = 'Patient not found';
            return view('admin/visits_dashboard', $data);
        }

        $data['patient'] = $patient;

        $visitModel = model('VisitModel');
        $visits = $visitModel->allForDate($patient['id'], $date);

        // Fetch attachments for each visit
        $db = \Config\Database::connect();
        foreach ($visits as &$v) {
            $atts = $db->table('attachments')
                       ->where('visit_id', $v['id'])
                       ->orderBy('id', 'ASC')
                       ->get()
                       ->getResultArray();
            $v['attachments'] = $atts;
        }

        $data['visits'] = $visits;

        // Default active = last sequence if not provided
        if ($data['active'] <= 0 && !empty($visits)) {
            $data['active'] = (int) end($visits)['sequence'];
        }

        return view('admin/visits_dashboard', $data);
    }
}
