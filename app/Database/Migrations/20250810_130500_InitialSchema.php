<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitialSchema extends Migration
{
    public function up()
    {
        $root = rtrim(ROOTPATH, '/');
        $dir  = $root . '/database/sql/initial';
        if (!is_dir($dir)) {
            throw new \RuntimeException("Initial SQL directory not found: " . $dir);
        }
        $files = glob($dir . '/*.sql') ?: [];
        sort($files);
        if (empty($files)) {
            throw new \RuntimeException("No .sql files found in " . $dir);
        }

        foreach ($files as $file) {
            $sql = file_get_contents($file);
            if ($sql === false) {
                throw new \RuntimeException("Unable to read SQL file: " . $file);
            }
            // normalize line endings
            $sql = str_replace(["\r\n","\r"], "\n", $sql);
            // strip /* */ comments
            $sql = preg_replace('#/\*.*?\*/#s', '', $sql);
            // remove -- comments
            $lines = [];
            foreach (explode("\n", $sql) as $line) {
                if (preg_match('/^\s*--/',$line)) continue;
                $lines[] = $line;
            }
            $sql = implode("\n", $lines);
            // split into statements
            $stmts = array_filter(array_map('trim', preg_split('/;\s*\n|;\s*$/m', $sql)));
            foreach ($stmts as $stmt) {
                if ($stmt !== '') {
                    $this->db->query($stmt);
                }
            }
        }
    }

    public function down()
    {
        $tables = [
            'api_tokens','users',
            'cert_generated','cert_templates',
            'reminders',
            'pos_invoice_items','pos_invoices',
            'preventive_events','preventive_items','preventive_plans','preventive_templates',
            'documents','visits',
            'visit_seq_counters','year_counters',
            'pets','breeds','species',
            'owner_mobiles','owners'
        ];
        foreach ($tables as $t) {
            try { $this->forge->dropTable($t, true); } catch (\Throwable $e) { /* ignore */ }
        }
    }
}
