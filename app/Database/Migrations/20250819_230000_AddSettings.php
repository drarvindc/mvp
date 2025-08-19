<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSettings extends Migration
{
    public function up()
    {
        // Create settings table if not exists (key/value)
        if (! $this->db->tableExists('settings')) {
            $this->forge->addField([
                'id'         => ['type'=>'BIGINT','unsigned'=>true,'auto_increment'=>true],
                'skey'       => ['type'=>'VARCHAR','constraint'=>120,'null'=>false,'unique'=>true],
                'svalue'     => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
                'updated_at' => ['type'=>'DATETIME','null'=>true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('settings', true);
        }

        // Insert default for auto_map_orphans = '1'
        $this->db->query("INSERT INTO settings (skey, svalue, updated_at)
                          VALUES ('auto_map_orphans','1', NOW())
                          ON DUPLICATE KEY UPDATE svalue=VALUES(svalue), updated_at=VALUES(updated_at)");
    }

    public function down()
    {
        // Keep the table; just unset the key if you roll back
        $this->db->table('settings')->where('skey', 'auto_map_orphans')->delete();
    }
}
