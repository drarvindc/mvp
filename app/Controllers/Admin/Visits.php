<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VisitModel;

class Visits extends BaseController
{
    protected $visitModel;
    protected $db;
    protected $patientModel;

    public function __construct()
    {
        $this->visitModel  = model(VisitModel::class);
        $this->patientModel = model('PatientModel'); // expects App\Models\PatientModel
        $this->db          = \Config\Database::connect();
    }

    /**
     * GET /admin/visits?uid=XXXX[&date=YYYY-MM-DD][&seq=N]
     */
    public function index()
    {
        $uid  = trim((string) $this->request->getGet('uid'));
        $date = trim((string) ($this->request->getGet('date') ?: date('Y-m-d')));
        $seqQ = $this->request->getGet('seq');

        if ($uid === '') {
            return $this->response->setStatusCode(400)->setBody('Missing uid');
        }

        // Fetch patient by UID (ensure your PatientModel has field "uid")
        $patient = $this->patientModel->where('uid', $uid)->first();
        if (!$patient) {
            return view('admin/visits_dashboard', [
                'error' => 'Patient not found',
                'uid'   => $uid,
                'date'  => $date,
                'visits'=> [],
                'activeSeq' => 0,
                'attachmentsByVisit' => []
            ]);
        }

        // Get all visits for the specified date
        $visits = $this->visitModel->allForDate($patient['id'], $date);

        // Determine active sequence (default to latest if exists)
        if ($seqQ !== null && $seqQ !== '') {
            $activeSeq = (int) $seqQ;
        } else {
            $activeSeq = count($visits) ? (int) end($visits)['sequence'] : 1;
        }

        // Load attachments grouped by sequence
        $attachmentsByVisit = [];
        foreach ($visits as $v) {
            $atts = $this->db->table('attachments')
                             ->where('visit_id', $v['id'])
                             ->orderBy('id', 'ASC')
                             ->get()
                             ->getResultArray();
            $attachmentsByVisit[(int)$v['sequence']] = $atts;
        }

        return view('admin/visits_dashboard', [
            'patient'            => $patient,
            'uid'                => $uid,
            'date'               => $date,
            'visits'             => $visits,
            'activeSeq'          => $activeSeq,
            'attachmentsByVisit' => $attachmentsByVisit,
        ]);
    }
}
