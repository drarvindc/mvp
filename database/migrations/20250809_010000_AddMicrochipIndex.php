<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMicrochipIndex extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE pets ADD INDEX idx_microchip (microchip)");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE pets DROP INDEX idx_microchip");
    }
}
