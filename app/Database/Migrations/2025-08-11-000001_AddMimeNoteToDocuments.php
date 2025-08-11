<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMimeNoteToDocuments extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // detect current columns
        $fields = [];
        try {
            foreach ($db->getFieldData('documents') as $f) {
                $fields[strtolower($f->name)] = true;
            }
        } catch (\Throwable $e) {
            // table not found, bail
            return;
        }

        // Add 'mime' if missing
        if (!isset($fields['mime'])) {
            $db->query("ALTER TABLE `documents` ADD COLUMN `mime` VARCHAR(100) NULL AFTER `filesize`");
        }

        // Add 'note' if missing
        if (!isset($fields['note'])) {
            $db->query("ALTER TABLE `documents` ADD COLUMN `note` TEXT NULL AFTER `mime`");
        }

        // Add 'pet_id' (nullable) if missing — optional, used by some features
        if (!isset($fields['pet_id'])) {
            $db->query("ALTER TABLE `documents` ADD COLUMN `pet_id` INT NULL AFTER `visit_id`");
            // Optional index for lookups
            $db->query("CREATE INDEX `idx_documents_pet_id` ON `documents` (`pet_id`)");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // Best-effort drop; some MySQL versions don't support DROP COLUMN IF EXISTS
        try { $db->query("ALTER TABLE `documents` DROP COLUMN `note`"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `documents` DROP COLUMN `mime`"); } catch (\Throwable $e) {}
        try { $db->query("ALTER TABLE `documents` DROP COLUMN `pet_id`"); } catch (\Throwable $e) {}
        try { $db->query("DROP INDEX `idx_documents_pet_id` ON `documents`"); } catch (\Throwable $e) {}
    }
}
