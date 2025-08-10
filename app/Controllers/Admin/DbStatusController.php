<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;

class DbStatusController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $tables = $db->listTables();
        $hasMigrationsTable = in_array('migrations', $tables, true);

        $lastBatchNum = 0;
        $applied = 0;
        if ($hasMigrationsTable) {
            $row = $db->table('migrations')->selectMax('batch')->get()->getRowArray();
            $lastBatchNum = $row ? (int)$row['batch'] : 0;
            $applied = $db->table('migrations')->countAllResults();
        }

        $migrations = \Config\Services::migrations();
        $found = $migrations->findMigrations(); // [path => class]
        $pendingFiles = [];
        foreach (array_keys($found) as $path) {
            $pendingFiles[] = basename($path);
        }

        return view('admin/dbstatus/index', [
            'now'           => Time::now('Asia/Kolkata')->toDateTimeString(),
            'lastBatch'     => $lastBatchNum,
            'appliedCount'  => $applied,
            'pendingFiles'  => $pendingFiles,
            'hasTable'      => $hasMigrationsTable,
        ]);
    }
}
