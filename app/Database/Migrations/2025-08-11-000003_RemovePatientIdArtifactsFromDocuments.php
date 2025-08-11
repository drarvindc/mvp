<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemovePatientIdArtifactsFromDocuments extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $forge = \Config\Database::forge();

        // 1) Drop foreign keys that reference documents.patient_id (if any)
        try {
            $sql = "SELECT CONSTRAINT_NAME
                      FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                     WHERE TABLE_SCHEMA = DATABASE()
                       AND TABLE_NAME = 'documents'
                       AND COLUMN_NAME = 'patient_id'
                       AND REFERENCED_TABLE_NAME IS NOT NULL";
            $constraints = $db->query($sql)->getResultArray();
            foreach ($constraints as $row) {
                $c = $row['CONSTRAINT_NAME'];
                try {
                    $db->query('ALTER TABLE `documents` DROP FOREIGN KEY `' . $c . '`');
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // 2) Drop indexes on documents.patient_id (if any)
        try {
            $idxSql = "SHOW INDEX FROM `documents`";
            $indexes = $db->query($idxSql)->getResultArray();
            $byKey = [];
            foreach ($indexes as $idx) {
                $key = $idx['Key_name'];
                $col = $idx['Column_name'];
                if (!isset($byKey[$key])) $byKey[$key] = [];
                $byKey[$key][] = $col;
            }
            foreach ($byKey as $name => $cols) {
                foreach ($cols as $col) {
                    if (strtolower($col) === 'patient_id') {
                        try {
                            $db->query('ALTER TABLE `documents` DROP INDEX `' . $name . '`');
                        } catch (\Throwable $e) {
                            // ignore
                        }
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // 3) Ensure we DO NOT add a patient_id column. Nothing else to add.
    }

    public function down()
    {
        // No-op: we are just removing bad constraints/indexes. Nothing to restore.
    }
}
