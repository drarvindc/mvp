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

// Visits Lite (public, no admin filter)
$routes->get('visits-lite', 'VisitsLite::index');

// ---------------------------
// API routes (public by design for now)
// ---------------------------
$routes->group('api', static function ($routes) {
    $routes->post('visit/open', 'Api\\VisitController::open');
    $routes->post('visit/upload', 'Api\\VisitController::upload');
    $routes->get('visit/today', 'Api\\VisitController::today'); // supports ?all=1 and optional &date=YYYY-MM-DD
});

// ---------------------------
// Admin tools (keyed) - keep OUTSIDE adminauth so ?key=... works
// ---------------------------
$routes->group('admin/tools', static function ($routes) {
    $routes->get('migrate', 'Admin\\MigrateController::index');
    $routes->get('api-tester', 'Admin\\Tools\\ApiTester::index');
});

// ---------------------------
// Admin area (requires login via adminauth filter)
// ---------------------------
$routes->group('admin', ['filter' => 'adminauth'], static function ($routes) {
    $routes->get('/', 'Admin\\Dashboard::index');
    $routes->get('visits', 'Admin\\Visits::index');
});
