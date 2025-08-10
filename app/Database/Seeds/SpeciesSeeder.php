<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $root = rtrim(ROOTPATH, '/');
        $dir  = $root . '/database/sql/seed';
        if (!is_dir($dir)) {
            throw new \RuntimeException("Seed SQL directory not found: " . $dir);
        }

        $files = glob($dir . '/*.sql') ?: [];
        $speciesFiles = [];
        $otherFiles   = [];
        foreach ($files as $f) {
            $name = strtolower(basename($f));
            if (strpos($name, 'species') !== false) $speciesFiles[] = $f; else $otherFiles[] = $f;
        }
        natsort($speciesFiles);
        natsort($otherFiles);
        $ordered = array_merge($speciesFiles, $otherFiles);

        $db->query('SET FOREIGN_KEY_CHECKS=0');

        foreach ($ordered as $file) {
            $sql = file_get_contents($file);
            if ($sql === false) {
                throw new \RuntimeException("Unable to read seed SQL file: " . $file);
            }
            $sql = str_replace(["\r\n", "\r"], "\n", $sql);
            $sql = preg_replace('#/\*.*?\*/#s', '', $sql);
            $lines = [];
            foreach (explode("\n", $sql) as $line) {
                if (preg_match('/^\s*--/', $line)) continue;
                $lines[] = $line;
            }
            $sql = implode("\n", $lines);

            $statements = array_filter(array_map('trim', preg_split('/;\s*\n|;\s*$/m', $sql)));

            foreach ($statements as $stmt) {
                if ($stmt === '') continue;
                if (preg_match('/^INSERT\s+INTO\s+species\s*\(/i', $stmt)) {
                    $stmt = preg_replace('/^INSERT\s+INTO/i', 'INSERT IGNORE INTO', $stmt, 1);
                }
                if (preg_match('/^INSERT\s+INTO\s+breeds\s*\(/i', $stmt)) {
                    $stmt = preg_replace('/^INSERT\s+INTO/i', 'INSERT IGNORE INTO', $stmt, 1);
                }
                $db->query($stmt);
            }
        }

        $db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}
