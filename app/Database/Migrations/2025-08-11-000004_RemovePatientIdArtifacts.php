<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemovePatientIdArtifacts extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1) Drop any indexes on `documents.patient_id`
        try {
            $idx = $db->query("SHOW INDEX FROM `documents`")->getResultArray();
            $toDrop = [];
            foreach ($idx as $row) {
                if (strcasecmp($row['Column_name'] ?? '', 'patient_id') === 0) {
                    $toDrop[$row['Key_name']] = true;
                }
            }
            foreach (array_keys($toDrop) as $key) {
                // MySQL: cannot DROP PRIMARY this way, but patient_id wouldn't be primary.
                $db->query("DROP INDEX `{$key}` ON `documents`");
            }
        } catch (\Throwable $e) {
            // ignore; table may not exist yet
        }

        // 2) Drop any FOREIGN KEY constraints that reference `patient_id`
        try {
            $create = $db->query("SHOW CREATE TABLE `documents`")->getRowArray();
            if (!empty($create['Create Table'])) {
                $sql = $create['Create Table'];
                if (preg_match_all('/CONSTRAINT `([^`]+)` FOREIGN KEY .*?\\(`patient_id`\\)/i', $sql, $m)) {
                    foreach ($m[1] as $fk) {
                        try {
                            $db->query("ALTER TABLE `documents` DROP FOREIGN KEY `{$fk}`");
                        } catch (\Throwable $e) { /* ignore */ }
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down()
    {
        // no-op (we're just cleaning invalid artifacts)
    }
}
