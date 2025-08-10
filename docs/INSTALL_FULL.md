# Vet CI4 Overlay — FULL (Ready to Drop on CI4 AppStarter)

1) Create subdomain pointing to CI4 `/public`
2) Upload CodeIgniter 4 AppStarter zip and extract
3) Upload **this overlay zip** and extract (MERGE/overwrite)
4) Copy `.env.example` → `.env` and set:
   - CI_ENVIRONMENT=production
   - app.baseURL='https://your-subdomain/'
   - MIGRATE_WEB_KEY=your-long-secret
   - DB credentials
5) Add routes (append to `app/Config/Routes.php`):
```
$routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
    $routes->get('migrate', 'Admin\MigrateController::index');
    $routes->post('migrate/run', 'Admin\MigrateController::run');
    $routes->post('migrate/rollback', 'Admin\MigrateController::rollback');
    $routes->post('migrate/seed-species', 'Admin\MigrateController::seedSpecies');
    $routes->get('db-status', 'Admin\DbStatusController::index');
});
```
6) Run in browser:
   - `/admin/tools/migrate?key=YOUR_SECRET` → **Run Migrations**
   - `/admin/tools/migrate?key=YOUR_SECRET` → **Seed Species & Breeds**
   - `/admin/tools/db-status?key=YOUR_SECRET` → verify
