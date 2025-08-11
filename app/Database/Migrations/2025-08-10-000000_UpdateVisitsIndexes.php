<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateVisitsIndexes extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Read current indexes
        try {
            $rows = $db->query("SHOW INDEX FROM `visits`")->getResultArray();
        } catch (\Throwable $e) {
            // Table missing? Nothing to do.
            return;
        }

        // Build index -> [unique(bool), cols(array in order)]
        $byKey = [];
        foreach ($rows as $r) {
            $key = $r['Key_name'];
            if (!isset($byKey[$key])) {
                $byKey[$key] = [
                    'unique' => ((int)($r['Non_unique'] ?? 1) === 0),
                    'cols'   => [],
                ];
            }
            $seq = (int)($r['Seq_in_index'] ?? 0);
            $col = strtolower((string)($r['Column_name'] ?? ''));
            $byKey[$key]['cols'][$seq] = $col; // keep order
        }
        // normalize order for comparison
        foreach ($byKey as &$info) {
            ksort($info['cols']);
            $info['cols'] = array_values($info['cols']);
        }
        unset($info);

        // Drop any UNIQUE index that exactly covers legacy or current pairs:
        // legacy: (patient_id, date)
        // current: (pet_id, visit_date)
        foreach ($byKey as $name => $info) {
            if (!$info['unique']) continue;
            $cols = $info['cols'];

            $isLegacy = ($cols === ['patient_id', 'date']);
            $isCurrent = ($cols === ['pet_id', 'visit_date']);

            if ($isLegacy || $isCurrent) {
                try {
                    $db->query("DROP INDEX `{$name}` ON `visits`");
                } catch (\Throwable $e) {
                    // ignore; proceed
                }
            }
        }

        // Ensure a non-unique composite index exists on (pet_id, visit_date, visit_seq)
        $haveComposite = false;
        // Re-read indexes after potential drops
        try {
            $rows = $db->query("SHOW INDEX FROM `visits`")->getResultArray();
        } catch (\Throwable $e) {
            return;
        }
        $byKey = [];
        foreach ($rows as $r) {
            $key = $r['Key_name'];
            if (!isset($byKey[$key])) {
                $byKey[$key] = [
                    'unique' => ((int)($r['Non_unique'] ?? 1) === 0),
                    'cols'   => [],
                ];
            }
            $seq = (int)($r['Seq_in_index'] ?? 0);
            $col = strtolower((string)($r['Column_name'] ?? ''));
            $byKey[$key]['cols'][$seq] = $col;
        }
        foreach ($byKey as $name => $info) {
            ksort($info['cols']);
            $cols = array_values($info['cols']);
            if (!$info['unique'] && $cols === ['pet_id', 'visit_date', 'visit_seq']) {
                $haveComposite = true;
                break;
            }
        }

        if (!$haveComposite) {
            try {
                $db->query("CREATE INDEX `idx_visits_pet_date_seq` ON `visits` (`pet_id`, `visit_date`, `visit_seq`)");
            } catch (\Throwable $e) {
                // If it already exists under another name, or MySQL version differences — ignore.
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        try {
            $db->query("DROP INDEX `idx_visits_pet_date_seq` ON `visits`");
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
