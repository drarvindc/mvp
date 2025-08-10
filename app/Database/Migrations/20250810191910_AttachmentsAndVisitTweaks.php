<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AttachmentsAndVisitTweaks_20250810191910 extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Create attachments if missing
        if (! $db->tableExists('attachments')) {
            $this->forge->addField([
                'id'         => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
                'visit_id'   => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
                'type'       => ['type'=>'VARCHAR','constraint'=>50],
                'filename'   => ['type'=>'VARCHAR','constraint'=>255],
                'filesize'   => ['type'=>'INT','constraint'=>11,'null'=>true],
                'mime'       => ['type'=>'VARCHAR','constraint'=>100,'null'=>true],
                'note'       => ['type'=>'TEXT','null'=>true],
                'created_at' => ['type'=>'DATETIME','null'=>true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('visit_id','visits','id','CASCADE','CASCADE');
            $this->forge->createTable('attachments', true);
        }

        // Add sequence/created_at to visits if missing
        $visitFields = [];
        try { $visitFields = array_map('strtolower', $db->getFieldNames('visits')); } catch (\Throwable $e) { $visitFields = []; }

        if (!in_array('sequence', $visitFields)) {
            $this->forge->addColumn('visits', [
                'sequence' => ['type'=>'INT','constraint'=>11,'default'=>1,'null'=>false, 'after'=>'visit_date']
            ]);
        }
        if (!in_array('created_at', $visitFields)) {
            $this->forge->addColumn('visits', [
                'created_at' => ['type'=>'DATETIME','null'=>true]
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('attachments')) {
            $this->forge->dropTable('attachments', true);
        }
        // do not drop visit columns
    }
}
