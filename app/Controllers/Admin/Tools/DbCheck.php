<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;

class DbCheck extends BaseController
{
    public function index()
    {
        /** @var BaseConnection $db */
        $db = \Config\Database::connect();
        $ok = $db->isConnected();

        $version = $db->getVersion();
        $migrations = null;
        try {
            $query = $db->query('SELECT COUNT(*) AS cnt FROM migrations');
            $row = $query->getRowArray();
            $migrations = $row['cnt'] ?? null;
        } catch (\Throwable $e) {
            $migrations = 'table_not_found';
        }

        $html = '<div style="font-family:system-ui,Arial,sans-serif;padding:16px;max-width:800px;margin:auto">';
        $html .= '<h2>DB Check</h2>';
        $html .= '<p><b>Status:</b> ' . ($ok ? 'Connected' : 'Not connected') . '</p>';
        $html .= '<p><b>Driver Version:</b> ' . htmlspecialchars((string) $version) . '</p>';
        $html .= '<p><b>migrations rows:</b> ' . htmlspecialchars((string) $migrations) . '</p>';
        $html .= '<hr><p style="color:#666">This page is read-only and safe. Remove route when not needed.</p>';
        $html .= '</div>';

        return $this->response->setBody($html);
    }
}
