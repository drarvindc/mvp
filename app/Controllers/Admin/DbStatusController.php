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
        $appliedClasses = [];
        if ($hasMigrationsTable) {
            $row = $db->table('migrations')->selectMax('batch')->get()->getRowArray();
            $lastBatchNum = $row ? (int)$row['batch'] : 0;
            $applied = $db->table('migrations')->countAllResults();
            $rows = $db->table('migrations')->select('class')->get()->getResultArray();
            foreach ($rows as $r) { $appliedClasses[] = $r['class']; }
        }

        $migrations = \Config\Services::migrations();
        $found = $migrations->findMigrations(); // map of path=>class OR class=>path depending on CI4 build

        $pendingFiles = [];
        foreach ($found as $k => $v) {
            $filepath = is_string($k) && is_file($k) ? $k : (is_string($v) && is_file($v) ? $v : null);
            $class    = is_string($v) && !is_file($v) ? $v : (is_string($k) && !is_file($k) ? $k : null);

            // If class is known and already applied, skip
            if ($class && in_array($class, $appliedClasses, true)) {
                continue;
            }

            // Show a clean filename when possible
            if ($filepath) {
                $pendingFiles[] = basename($filepath);
            } elseif ($class) {
                $pendingFiles[] = $class;
            }
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
