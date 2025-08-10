<?php
// Paste into app/Config/Routes.php (near the bottom)

$routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
    $routes->get('migrate', 'Admin\MigrateController::index');
    $routes->post('migrate/run', 'Admin\MigrateController::run');
    $routes->post('migrate/rollback', 'Admin\MigrateController::rollback');
    $routes->post('migrate/seed-species', 'Admin\MigrateController::seedSpecies');

    $routes->get('db-status', 'Admin\DbStatusController::index');
});

// Optional diagnostics (remove after testing)
// $routes->get('testping', static fn()=> 'pong '.date('c'));
// $routes->get('admin/tools/migrate/ping', static fn()=> 'migrate-route-ok');
