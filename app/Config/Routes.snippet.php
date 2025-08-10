// --- Admin-only Migrations Routes ---
// In app/Config/Routes.php add:
// $routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
//     $routes->get('migrate', 'Admin\MigrateController::index');
//     $routes->post('migrate/run', 'Admin\MigrateController::run');
//     $routes->post('migrate/rollback', 'Admin\MigrateController::rollback');
//     $routes->post('migrate/seed-species', 'Admin\MigrateController::seedSpecies');
// });
//
// In app/Config/Filters.php register the filter:
// public $aliases = [
//   'adminauth' => \App\Filters\AdminAuth::class,
//   // ...
// ];
