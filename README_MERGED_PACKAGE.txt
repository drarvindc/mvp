Vet CI4 DB Suite (Merged Package)
=================================

Folders included:
- database/           ← initial schema, seeds, CI4 migrations, SQL deltas
- app/                ← admin-only migration web route + DB status controller/views
- docs/               ← usage guides and changelogs

How to install into your CodeIgniter 4 project:
1) Unzip this package locally.
2) Copy the `database/`, `app/`, and `docs/` folders into the ROOT of your CI4 project.
   - Allow it to MERGE with existing folders.
3) Edit app/Config/Routes.php and add:
   $routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
       $routes->get('migrate', 'Admin\MigrateController::index');
       $routes->post('migrate/run', 'Admin\MigrateController::run');
       $routes->post('migrate/rollback', 'Admin\MigrateController::rollback');
       $routes->post('migrate/seed-species', 'Admin\MigrateController::seedSpecies');
       $routes->get('db-status', 'Admin\DbStatusController::index');
   });
4) Edit app/Config/Filters.php and register:
   public $aliases = [
     'adminauth' => \App\Filters\AdminAuth::class,
     // ...
   ];
5) In `.env`, set:
   MIGRATE_WEB_KEY=your-long-random-secret
6) Visit the tools:
   - Run migrations:  https://yourdomain/admin/tools/migrate?key=your-long-random-secret
   - DB status:       https://yourdomain/admin/tools/db-status?key=your-long-random-secret

Option A (phpMyAdmin):
- For fresh install, import: database/sql/initial/*_initial_schema.sql
- Then import seeds in database/sql/seed/
- Future changes: apply files in database/sql/deltas/ in chronological order

Option B (CI4 CLI):
- php spark migrate
- php spark db:seed SpeciesSeeder
