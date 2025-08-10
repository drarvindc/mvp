<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClinicSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'key' => ['type' => 'VARCHAR', 'constraint' => 120, 'unique' => true],
            'value' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('clinic_settings', true);
    }

    public function down()
    {
        $this->forge->dropTable('clinic_settings', true);
    }
}
