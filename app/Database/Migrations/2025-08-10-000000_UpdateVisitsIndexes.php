<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateVisitsIndexes extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Find & drop any unique index that exactly covers (patient_id, date)
        $indexes = $db->query("SHOW INDEX FROM `visits`")->getResultArray();
        $byKey   = [];

        foreach ($indexes as $idx) {
            $key    = $idx['Key_name'];
            $column = $idx['Column_name'];

            if (!isset($byKey[$key])) {
                $byKey[$key] = ['cols' => [], 'unique' => ($idx['Non_unique'] == 0)];
            }
            $byKey[$key]['cols'][] = $column;
        }

        foreach ($byKey as $name => $info) {
            $cols = array_map('strtolower', $info['cols']);
            sort($cols);
            if ($info['unique'] && $cols === ['date', 'patient_id']) {
                $db->query("ALTER TABLE `visits` DROP INDEX `" . $name . "`");
            }
        }

        // Ensure a non-unique composite index exists
        $db->query("CREATE INDEX `idx_visits_patient_date_seq` ON `visits` (`patient_id`, `date`, `sequence`)");
    }

    public function down()
    {
        $db = \Config\Database::connect();
        // Best-effort cleanup only
        try {
            $db->query("DROP INDEX `idx_visits_patient_date_seq` ON `visits`");
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
