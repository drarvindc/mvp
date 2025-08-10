<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;

class DbStatusController extends BaseController
{
    public function index()
    {
        // Check pending migrations
        $migrations = \Config\Services::migrations();
        $pending = $migrations->findMigrations();
        $lastBatch = $this->db->table('migrations')->selectMax('batch')->get()->getRowArray();
        $lastBatchNum = $lastBatch ? $lastBatch['batch'] : 0;
        $applied = $this->db->table('migrations')->countAllResults();

        return view('admin/dbstatus/index', [
            'now' => Time::now('Asia/Kolkata')->toDateTimeString(),
            'lastBatch' => $lastBatchNum,
            'appliedCount' => $applied,
            'pendingFiles' => array_keys($pending),
        ]);
    }
}
