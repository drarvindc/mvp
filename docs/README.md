# CI4 Admin Patch (DB Status + AdminAuth)

This patch adds:
- A tolerant DB Status page (works even before the first migration)
- AdminAuth filter (protects routes with admin session or ?key=... token)

## Files
- app/Controllers/Admin/DbStatusController.php
- app/Views/admin/dbstatus/index.php
- app/Filters/AdminAuth.php

## Install
1) Upload and extract this zip in your CI4 project root (merge into `app/`).
2) Ensure route exists in `app/Config/Routes.php`:
   $routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
       $routes->get('db-status', 'Admin\DbStatusController::index');
   });
3) Ensure filter alias in `app/Config/Filters.php`:
   public $aliases = [
     'adminauth' => \App\Filters\AdminAuth::class,
     // existing aliases...
   ];
4) In `.env`, set:
   MIGRATE_WEB_KEY=your-long-random-secret
   app.baseURL='https://yourdomain/'
5) Visit:
   https://yourdomain/admin/tools/db-status?key=your-long-random-secret
