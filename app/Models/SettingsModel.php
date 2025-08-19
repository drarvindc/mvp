<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

/**
 * Resilient key/value settings store.
 * Works with table `settings` whose PK column is one of:
 *   - key
 *   - name
 *   - setting_key
 *
 * Minimal schema (any PK name is fine, see above):
 *   CREATE TABLE IF NOT EXISTS `settings` (
 *     `<PK>`      varchar(100) NOT NULL PRIMARY KEY,
 *     `value`     text NOT NULL,
 *     `updated_at` datetime NOT NULL
 *   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */
class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'key';          // will be adjusted in __construct
    protected $useAutoIncrement = false;

    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields    = ['key', 'value', 'updated_at']; // will be adjusted in __construct

    /** Actual PK column name we detected (e.g., 'key' or 'name') */
    private string $pk = 'key';

    public function __construct(?\CodeIgniter\Database\ConnectionInterface $db = null, ?\CodeIgniter\Validation\ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        // Detect actual PK column in the existing `settings` table
        $fields = [];
        try {
            $fields = $this->db->getFieldNames($this->table);
        } catch (\Throwable $e) {
            // table might not exist yet; leave defaults so migrations can run
            $fields = [];
        }

        // Pick first matching PK name we find
        foreach (['key', 'name', 'setting_key'] as $candidate) {
            if (in_array($candidate, $fields, true)) {
                $this->pk = $candidate;
                $this->primaryKey = $candidate;
                break;
            }
        }

        // Align allowedFields to the detected PK
        $this->allowedFields = [$this->pk, 'value', 'updated_at'];
    }

    /**
     * Get a setting value (string) or default.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        // Use builder to avoid relying on Model::find() with dynamic PK uncertainty
        $row = $this->builder()->where($this->pk, $key)->get()->getRowArray();
        return $row['value'] ?? $default;
    }

    /**
     * Upsert a setting value (named `put` to avoid clashing with CI4 Model::set()).
     */
    public function put(string $key, ?string $value): void
    {
        $now = date('Y-m-d H:i:s');

        // Try update, then insert if missing
        $builder = $this->builder();

        $builder->where($this->pk, $key)->update([
            'value'      => $value ?? '',
            'updated_at' => $now,
        ]);

        if ($this->db->affectedRows() === 0) {
            // No row updated; insert new
            $builder->insert([
                $this->pk     => $key,
                'value'       => $value ?? '',
                'updated_at'  => $now,
            ]);
        }
    }

    /**
     * Return all settings as key => value array (using detected PK column).
     */
    public function getAllIndexed(): array
    {
        $out = [];
        foreach ($this->builder()->get()->getResultArray() as $row) {
            if (! array_key_exists($this->pk, $row)) {
                // If schema is unexpected, fail loudly to avoid silent corruption
                throw new RuntimeException("Settings table missing PK column '{$this->pk}'.");
            }
            $out[$row[$this->pk]] = $row['value'] ?? null;
        }
        return $out;
    }
}
