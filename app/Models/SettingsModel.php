<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Simple key/value settings store.
 * Table schema expected:
 *   CREATE TABLE IF NOT EXISTS `settings` (
 *     `key`        varchar(100) NOT NULL PRIMARY KEY,
 *     `value`      text NOT NULL,
 *     `updated_at` datetime NOT NULL
 *   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */
class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'key';
    protected $useAutoIncrement = false;

    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields    = ['key', 'value', 'updated_at'];

    /**
     * Get a setting value (string) or default.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $row = $this->find($key);
        return $row['value'] ?? $default;
    }

    /**
     * Upsert a setting value. (Named `put` to avoid clashing with CI4 Model::set)
     */
    public function put(string $key, ?string $value): void
    {
        $this->save([
            'key'        => $key,
            'value'      => $value ?? '',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Return all settings as key => value array.
     */
    public function getAllIndexed(): array
    {
        $out = [];
        foreach ($this->findAll() as $row) {
            $out[$row['key']] = $row['value'];
        }
        return $out;
    }
}
