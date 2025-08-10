<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('patient/intake/raw', static function () { return 'intake route OK'; });

$routes->get('patient/intake', 'PatientController::intake');
$routes->post('patient/find', 'PatientController::find');
$routes->get('patient/provisional', 'PatientController::provisional');
$routes->get('patient/print-existing', 'PatientController::printExisting');
$routes->post('patient/provisional/create', 'PatientController::provisionalCreate');

$routes->get('media/ping', 'MediaController::ping');
$routes->get('media/selftest', 'MediaController::selftest');
$routes->get('media/qr-uid', 'MediaController::qrUid');
$routes->get('media/barcode-uid', 'MediaController::barcodeUid');

$routes->group('api', ['filter' => 'apiauth'], static function($routes){
$routes->post('visit/open', 'Api\\VisitController::open');
$routes->post('visit/upload', 'Api\\VisitController::upload');
$routes->get('visit/today', 'Api\\VisitController::today');
$routes->get('visit/by-date', 'Api\\VisitController::byDate');
});

$routes->post('api/visit/open',   'VisitController::open');
$routes->post('api/visit/upload', 'VisitController::upload');
$routes->get('api/visit/today',   'VisitController::today');

$routes->get('admin/visits', 'Admin\\Visits::index', ['filter'=>'adminauth']);
$routes->get('api/visit/today', 'VisitController::today');



// ... keep your existing routes above

$routes->post('api/visit/open',   'VisitController::open');
$routes->post('api/visit/upload', 'VisitController::upload');
$routes->get('api/visit/today',   'VisitController::today');

// ... keep your existing routes below

// Minimal admin viewer (your 'adminauth' filter already exists)
$routes->get('admin/visit/view', 'Admin\\VisitViewController::view', ['filter'=>'adminauth']);
$routes->get('admin/visit/file', 'Admin\\VisitViewController::file', ['filter'=>'adminauth']);

$routes->get('admin/tools/api-tester', 'Admin\\ApiTesterController::index', ['filter'=>'adminauth']);


$routes->get('patient/debug/by-mobile', 'PatientController::debugByMobile');




$routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
      $routes->get('diagnostics', 'Admin\\DiagnosticsController::index');
    $routes->get('diagnostics/db-check', 'Admin\\DiagnosticsController::dbCheck');
    $routes->get('diagnostics/env-check', 'Admin\\DiagnosticsController::envCheck');
    $routes->get('migrate', 'Admin\MigrateController::index');
    $routes->post('migrate/run', 'Admin\MigrateController::run');
    $routes->post('migrate/rollback', 'Admin\MigrateController::rollback');
    $routes->post('migrate/seed-species', 'Admin\MigrateController::seedSpecies');
    
    $routes->get('patient/intake', 'PatientController::intake');
$routes->post('patient/find', 'PatientController::find');
$routes->get('patient/provisional', 'PatientController::provisional');

    $routes->get('db-status', 'Admin\DbStatusController::index');
	
	
});

// Optional diagnostics (remove after testing)
// $routes->get('testping', static fn()=> 'pong '.date('c'));
// $routes->get('admin/tools/migrate/ping', static fn()=> 'migrate-route-ok');