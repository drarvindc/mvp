<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixDocumentsColumns extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Get existing columns safely
        $cols = [];
        try {
            foreach ($db->getFieldData('documents') as $f) {
                $cols[strtolower($f->name)] = true;
            }
        } catch (\Throwable $e) {
            // documents table not found; nothing to do
            return;
        }

        // Add 'pet_id' if missing (nullable)
        if (!isset($cols['pet_id'])) {
            $db->query("ALTER TABLE `documents` ADD COLUMN `pet_id` INT NULL AFTER `visit_id`");
            // Create index only if column truly exists now
            try {
                $db->query("CREATE INDEX `idx_documents_pet_id` ON `documents` (`pet_id`)");
            } catch (\Throwable $e) {
                // ignore if index already exists or engine doesn't support
            }
        }

        // Refresh columns list
        $cols = [];
        foreach ($db->getFieldData('documents') as $f) {
            $cols[strtolower($f->name)] = true;
        }

        // Add 'mime' if missing
        if (!isset($cols['mime'])) {
            $db->query("ALTER TABLE `documents` ADD COLUMN `mime` VARCHAR(100) NULL AFTER `filesize`");
        }

        // Add 'note' if missing
        if (!isset($cols['note'])) {
            $db->query("ALTER TABLE `documents` ADD COLUMN `note` TEXT NULL AFTER `mime`");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        // Best-effort drops
        try { $db->query("ALTER TABLE `documents` DROP COLUMN `note`"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `documents` DROP COLUMN `mime`"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `documents` DROP COLUMN `pet_id`"); } catch (\Throwable $e) {}
        try { $db->query("DROP INDEX `idx_documents_pet_id` ON `documents`"); } catch (\Throwable $e) {}
    }
}
