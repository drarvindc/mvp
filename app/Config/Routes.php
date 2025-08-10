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

// Public Visits Lite page (no auth)
$routes->get('visits-lite', 'VisitsLite::index');

// ---------------------------
// API (public for now)
// ---------------------------
$routes->group('api', static function ($routes) {
    $routes->get('visit/today', 'Api\\VisitController::today');   // supports ?uid=XXXX&date=YYYY-MM-DD&all=1
    $routes->post('visit/open', 'Api\\VisitController::open');    // supports forceNewVisit=1
    // $routes->post('visit/upload', 'Api\\VisitController::upload'); // optional
});

// ---------------------------
// Admin tools (keyed), keep outside adminauth
// ---------------------------
$routes->group('admin/tools', static function ($routes) {
    $routes->get('migrate', 'Admin\\MigrateController::index');
    $routes->post('migrate/run', 'Admin\\MigrateController::run');
    $routes->post('migrate/rollback', 'Admin\\MigrateController::rollback');
    $routes->post('migrate/seed-species', 'Admin\\MigrateController::seedSpecies');
});

// ---------------------------
// Admin area (protected)
// ---------------------------
$routes->group('admin', ['filter' => 'adminauth'], static function ($routes) {
    $routes->get('/', 'Admin\\Dashboard::index');
    $routes->get('visits', 'Admin\\Visits::index');
    $routes->get('db-status', 'Admin\\DbStatusController::index');
});
