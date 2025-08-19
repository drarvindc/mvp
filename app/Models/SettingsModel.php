<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'key';
    protected $useAutoIncrement = false;

    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields    = ['key', 'value', 'updated_at'];

    public function get(string $key, ?string $default = null): ?string
    {
        $row = $this->find($key);
        return $row['value'] ?? $default;
    }

    public function set(string $key, ?string $value): void
    {
        $this->save([
            'key'        => $key,
            'value'      => $value ?? '',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getAllIndexed(): array
    {
        $out = [];
        foreach ($this->findAll() as $row) {
            $out[$row['key']] = $row['value'];
        }
        return $out;
    }
}
