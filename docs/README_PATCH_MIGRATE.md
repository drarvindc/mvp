# Patch: Migration Page (No Internal Redirect)

This patch provides a minimal MigrateController that relies only on the `adminauth` route filter,
so it won't self-redirect if the session/key is okay. It also includes a simple AdminLTE view.

## Files
- app/Controllers/Admin/MigrateController.php
- app/Views/admin/migrate/run.php

## Install
1) Upload and extract into your CI4 project root (merge into `app/`).
2) Ensure routes in `app/Config/Routes.php`:
   $routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
       $routes->get('migrate', 'Admin\MigrateController::index');
       $routes->post('migrate/run', 'Admin\MigrateController::run');
       $routes->post('migrate/rollback', 'Admin\MigrateController::rollback');
       $routes->post('migrate/seed-species', 'Admin\MigrateController::seedSpecies');
   });
3) Ensure `.env` has:
   app.baseURL='https://yourdomain/'
   MIGRATE_WEB_KEY=your-long-random-secret
