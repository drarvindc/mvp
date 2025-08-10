<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// ---------------------------
// Public routes
// ---------------------------
$routes->get('/', 'Home::index');

// Patient intake (public)
$routes->get('patient/intake/raw', static function () { return 'intake route OK'; });
$routes->get('patient/intake', 'PatientController::intake');
$routes->post('patient/find', 'PatientController::find');
$routes->get('patient/provisional', 'PatientController::provisional');
$routes->get('patient/print-existing', 'PatientController::printExisting');
$routes->post('patient/provisional/create', 'PatientController::provisionalCreate');

// Visits Lite (public, no admin filter)
$routes->get('visits-lite', 'VisitsLite::index');

// ---------------------------
// API routes (public by design, can be throttled later)
// ---------------------------
$routes->group('api', static function ($routes) {
    $routes->post('visit/open', 'VisitController::open');
    $routes->post('visit/upload', 'VisitController::upload');
    $routes->get('visit/today', 'VisitController::today'); // supports ?all=1
});

// ---------------------------
/**
 * Admin tools that you already access with a key in the URL.
 * Keep these OUTSIDE the adminauth group so they continue to work
 * with the ?key=... query param.
 */
// ---------------------------
$routes->group('admin/tools', static function ($routes) {
    $routes->get('migrate', 'Admin\\MigrateController::index');
    $routes->post('migrate/run', 'Admin\\MigrateController::run');
    $routes->post('migrate/rollback', 'Admin\\MigrateController::rollback');
    $routes->post('migrate/seed-species', 'Admin\\MigrateController::seedSpecies');
});

// ---------------------------
// Admin area (protected by adminauth filter)
// ---------------------------
$routes->group('admin', ['filter' => 'adminauth'], static function ($routes) {
    $routes->get('/', 'Admin\\Dashboard::index');
    $routes->get('visits', 'Admin\\Visits::index');      // full admin dashboard (requires login)
    $routes->get('db-status', 'Admin\\DbStatusController::index');
});

// Optional diagnostics (commented)
// $routes->get('testping', static fn() => 'pong ' . date('c'));
// $routes->get('admin/tools/migrate/ping', static fn() => 'migrate-route-ok');
