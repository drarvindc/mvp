<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Settings model for schema:
 *   id (PK, auto inc), skey (unique), svalue, updated_at
 */
class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    // we update rows by skey; id is auto
    protected $allowedFields    = ['skey', 'svalue', 'updated_at'];

    /**
     * Get a setting by skey.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $row = $this->builder()
            ->select('svalue')
            ->where('skey', $key)
            ->get()
            ->getRowArray();

        return $row['svalue'] ?? $default;
    }

    /**
     * Upsert a setting by skey.
     */
    public function put(string $key, ?string $value): void
    {
        $now = date('Y-m-d H:i:s');

        // Try update first
        $this->builder()
            ->where('skey', $key)
            ->update([
                'svalue'     => $value ?? '',
                'updated_at' => $now,
            ]);

        if ($this->db->affectedRows() === 0) {
            // Insert if not present (skey is UNIQUE)
            $this->builder()->insert([
                'skey'       => $key,
                'svalue'     => $value ?? '',
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Return all settings as skey => svalue.
     */
    public function getAllIndexed(): array
    {
        $out = [];
        foreach ($this->builder()->select('skey, svalue')->get()->getResultArray() as $row) {
            $out[$row['skey']] = $row['svalue'];
        }
        return $out;
    }
}
