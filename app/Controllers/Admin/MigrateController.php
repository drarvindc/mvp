<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class MigrateController extends BaseController
{
    protected function ensureAuthorized()
    {
        // Simple guard: require admin session OR valid token in query/header.
        $sessionRole = session('role');
        $headerToken = $this->request->getHeaderLine('X-Migrate-Key');
        $queryToken  = $this->request->getGet('key');
        $envToken    = getenv('MIGRATE_WEB_KEY') ?: '';

        if ($sessionRole === 'admin') return;
        if ($envToken && ($headerToken === $envToken || $queryToken === $envToken)) return;

        return redirect()->to('/')->with('error', 'Unauthorized.');
    }

    public function index()
    {
        if ($resp = $this->ensureAuthorized()) return $resp;
        return view('admin/migrate/run', [
            'now' => Time::now('Asia/Kolkata')->toDateTimeString(),
        ]);
    }

    public function run()
    {
        if ($resp = $this->ensureAuthorized()) return $resp;
        $migrations = \Config\Services::migrations();
        try {
            $migrations->latest();
            return redirect()->back()->with('message', 'Migrations applied successfully.');
        } catch (\Throwable $e) {
            log_message('error', 'Migration run failed: {error}', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }

    public function rollback()
    {
        if ($resp = $this->ensureAuthorized()) return $resp;
        $migrations = \Config\Services::migrations();
        try {
            // Rollback 1 batch
            $migrations->regress(-1);
            return redirect()->back()->with('message', 'Rolled back last batch.');
        } catch (\Throwable $e) {
            log_message('error', 'Migration rollback failed: {error}', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Rollback failed: ' . $e->getMessage());
        }
    }

    public function seedSpecies()
    {
        if ($resp = $this->ensureAuthorized()) return $resp;
        try {
            $seeder = \Config\Database::seeder();
            $seeder->call('App\\Database\\Seeds\\SpeciesSeeder');
            return redirect()->back()->with('message', 'Species & sample breeds seeded.');
        } catch (\Throwable $e) {
            log_message('error', 'Seeding failed: {error}', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Seeding failed: ' . $e->getMessage());
        }
    }
}
