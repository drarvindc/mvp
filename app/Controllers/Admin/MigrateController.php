<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class MigrateController extends BaseController
{
    public function index()
    {
        // No auth logic here; route filter handles it
        return view('admin/migrate/run');
    }

    public function run()
    {
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
        $migrations = \Config\Services::migrations();
        try {
            $migrations->regress(-1);
            return redirect()->back()->with('message', 'Rolled back last batch.');
        } catch (\Throwable $e) {
            log_message('error', 'Migration rollback failed: {error}', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Rollback failed: ' . $e->getMessage());
        }
    }

    public function seedSpecies()
    {
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
