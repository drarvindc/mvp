<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Visits extends BaseController
{
    protected $db;
    protected $tz = 'Asia/Kolkata';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        date_default_timezone_set($this->tz);
        helper(['url']);
    }

    public function index()
    {
        $uid  = trim((string) $this->request->getGet('uid'));
        $date = $this->request->getGet('date') ?: date('Y-m-d');
        $seq  = (int) ($this->request->getGet('seq') ?? 0);

        $patient = null;
        $visits  = [];
        $activeSeq = $seq > 0 ? $seq : 1;
        $attachmentsByVisit = [];

        if ($uid !== '') {
            // pets.unique_id -> pets row
            $patient = $this->db->table('pets p')
                                ->select('p.id as pet_id, p.unique_id, p.pet_name, p.species_id, p.breed_id')
                                ->where('p.unique_id', $uid)
                                ->get()->getRowArray();

            if ($patient) {
                // fetch all visits on the date
                $visits = $this->db->table('visits')
                                   ->where(['pet_id'=>$patient['pet_id'],'visit_date'=>$date])
                                   ->orderBy('sequence','ASC')
                                   ->get()->getResultArray();

                if (!empty($visits)) {
                    // default to last sequence if not specified
                    $activeSeq = $seq > 0 ? $seq : (int) end($visits)['sequence'];

                    // attachments for each
                    foreach ($visits as $v) {
                        $atts = $this->db->table('attachments')
                                         ->where('visit_id', $v['id'])
                                         ->orderBy('id','ASC')
                                         ->get()->getResultArray();
                        $attachmentsByVisit[(int)$v['sequence']] = $atts;
                    }
                }
            }
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
