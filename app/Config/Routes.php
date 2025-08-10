<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
    $routes->get('migrate', 'Admin\\MigrateController::index');
    $routes->post('migrate/run', 'Admin\\MigrateController::run');
    $routes->post('migrate/rollback', 'Admin\\MigrateController::rollback');
    $routes->post('migrate/seed-species', 'Admin\\MigrateController::seedSpecies');
    $routes->get('db-status', 'Admin\\DbStatusController::index');
});

