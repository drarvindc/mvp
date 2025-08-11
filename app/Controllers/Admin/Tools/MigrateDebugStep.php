<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;
use CodeIgniter\Database\MigrationRunner;

class MigrateDebugStep extends BaseController
{
    public function index()
    {
        $key = $this->request->getGet('key');
        if ($key !== 'arvindrchauhan1723') {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }

        helper('filesystem');

        $service = \Config\Services::migrations();
        $service->setNamespace('App');

        // Find all migration files
        $files = service('locator')->listFiles('Database/Migrations');
        natsort($files);
        $allMigrations = [];
        foreach ($files as $file) {
            $class = $this->classFromFile($file);
            $allMigrations[] = [
                'file'  => $file,
                'class' => $class,
                'ran'   => $this->hasRun($class),
            ];
        }

        $ranClass = null;
        $errorMsg = null;

        if ($this->request->getGet('step') === '1') {
            // Get first not-run migration
            $next = null;
            foreach ($allMigrations as $m) {
                if (!$m['ran']) { $next = $m; break; }
            }

            if (!$next) {
                $ranClass = 'All migrations have run. Nothing pending.';
            } else {
                try {
                    $runner = new MigrationRunner( config('Migrations'), service('logger'), service('db') );
                    $runner->setNamespace('App');
                    $runner->version($next['class']); // run this migration only
                    $ranClass = 'Ran: ' . $next['class'];
                } catch (\Throwable $e) {
                    $ranClass = 'FAILED migration: ' . $next['class'];
                    $errorMsg = $e->getMessage();
                }
            }
        }

        return view('admin/tools/migrate_debug_step', [
            'files'    => $allMigrations,
            'ranClass' => $ranClass,
            'errorMsg' => $errorMsg,
        ]);
    }

    private function classFromFile(string $path): string
    {
        $code = @file_get_contents($path) ?: '';
        if (preg_match('/class\s+([A-Za-z0-9_]+)/', $code, $m)) {
            return 'App\\Database\\Migrations\\' . $m[1];
        }
        return basename($path);
    }

    private function hasRun(string $class): bool
    {
        $db = \Config\Database::connect();
        $row = $db->table('migrations')->where('class', $class)->get()->getRowArray();
        return !empty($row);
    }
}
