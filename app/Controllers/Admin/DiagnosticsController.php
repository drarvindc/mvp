<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DiagnosticsController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $info = [
            'appBaseURL' => config('App')->baseURL,
            'environment' => env('CI_ENVIRONMENT', 'production'),
            'db' => [
                'database' => $db->getDatabase(),
                'driver'   => $db->DBDriver,
                'host'     => $db->hostname ?? 'localhost',
                'tables'   => $db->listTables(),
            ],
            'php' => [
                'version' => PHP_VERSION,
            ],
            'time' => date('c'),
        ];
        return view('admin/diagnostics/index', ['info' => $info]);
    }

    public function dbCheck()
    {
        $db = \Config\Database::connect();
        $tables = $db->listTables();
        return 'DB=' . $db->getDatabase() . ' tables=' . count($tables);
    }

    public function envCheck()
    {
        $key = getenv('MIGRATE_WEB_KEY') ?: '';
        return 'env_ok baseURL=' . config('App')->baseURL . ' key_len=' . strlen($key);
    }
}
