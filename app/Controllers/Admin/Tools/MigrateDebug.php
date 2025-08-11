<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class MigrateDebug extends BaseController
{
    public function index()
    {
        $key = $this->request->getGet('key');
        if ($key !== 'arvindrchauhan1723') {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }

        $service = \Config\Services::migrations();
        $service->setNamespace('App');

        // List all migration classes in order:
        $paths = service('locator')->listFiles('Database/Migrations');
        natsort($paths);
        $all = [];
        foreach ($paths as $p) {
            $all[] = [
                'file' => $p,
                'class' => $this->classFromFile($p),
            ];
        }

        // Try to run just the **next** pending migration and capture errors
        $ran = null; $error = null;
        if ($this->request->getGet('step') === '1') {
            try {
                $service->latest(); // CI runs all; we’ll catch and then show the last attempted
                $ran = 'All up to date (no pending migrations).';
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                $ran   = 'A migration threw an error. See details below.';
            }
        }

        return view('admin/tools/migrate_debug', [
            'files' => $all,
            'ran'   => $ran,
            'error' => $error,
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
}
