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

$routes->get('admin/tools/migrate-debug', 'Admin\Tools\MigrateDebug::index');
$routes->get('admin/tools/migrate-debug-step', 'Admin\Tools\MigrateDebugStep::index');
$routes->get('tools/visits-admin-view', 'Admin\\Visits::index'); // temporary mirror
$routes->get('visits-lite', 'VisitsLite::index');

// Login routes (outside adminauth)
$routes->get('admin/login', 'Admin\Auth\Login::index');
$routes->post('admin/login', 'Admin\Auth\Login::attempt');
$routes->get('admin/logout', 'Admin\Auth\Login::logout');
$routes->get('admin/logout-all', 'Admin\Auth\Login::logoutAll');

// One-time bootstrap to create admin user (keyed)
$routes->get('admin/tools/make-admin', 'Admin\Tools\MakeAdmin::index');

// Protect your admin area with the adminauth filter
$routes->group('admin', ['filter'=>'adminauth'], static function($routes) {

$routes->get('tools/api-tester', 'Admin\Tools\ApiTester::index', ['filter' => 'devopenaccess']);
$routes->get('tools/api-tester-android', 'Admin\Tools\ApiTesterAndroid::index', ['filter' => 'devopenaccess']);
$routes->get('tools/api-tester-classic', 'Admin\Tools\ApiTesterClassic::index', ['filter' => 'devopenaccess']);
$routes->get('tools/stable-api-tester', 'Admin\Tools\StableApiTester::index', ['filter' => 'devopenaccess']);
$routes->get('tools/db-check', 'Admin\Tools\DbCheck::index', ['filter' => 'devopenaccess']);

    // your admin routes here
});


// --- Stable API replacing main /api/visit/* ---
$routes->group('api/visit', ['filter'=>'stableapiauth'], static function($routes) {
    $routes->post('open',   'Stable\VisitController::open');
    $routes->post('upload', 'Stable\VisitController::upload');
    $routes->get('today',   'Stable\VisitController::today');
    $routes->get('by-date', 'Stable\VisitController::byDate');
});

// Tip: If you still have older /api/visit/* routes defined elsewhere, comment them out so these take precedence.

// Stable API (protected)
$routes->group('stable-api', ['filter'=>'stableapiauth'], static function($routes) {
    $routes->post('visit/open', 'Stable\VisitController::open');
    $routes->post('visit/upload', 'Stable\VisitController::upload');
    $routes->get('visit/today', 'Stable\VisitController::today');
    $routes->get('visit/by-date', 'Stable\VisitController::byDate');
});

// Stable tester (keyed)
$routes->get('tools/stable-api-tester', 'Admin\Tools\StableApiTester::index');


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
$routes->get('admin/visits-lite', 'Admin\\VisitsLite::index');




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
	$routes->get('api-tester', 'Admin\\Tools\\ApiTester::index');
	$routes->get('api-tester-android', 'Admin\\Tools\\ApiTesterAndroid::index');
	$routes->get('api-tester-android-classic', 'Admin\Tools\ApiTesterAndroidClassic::index');
	$routes->get('api-tester-classic', 'Admin\Tools\ApiTesterClassic::index');
    
    $routes->get('patient/intake', 'PatientController::intake');
$routes->post('patient/find', 'PatientController::find');
$routes->get('patient/provisional', 'PatientController::provisional');

    $routes->get('db-status', 'Admin\DbStatusController::index');
	
	
});

// Optional diagnostics (remove after testing)
// $routes->get('testping', static fn()=> 'pong '.date('c'));
// $routes->get('admin/tools/migrate/ping', static fn()=> 'migrate-route-ok');

// --- Added by patch: public tester routes (keep outside adminauth) ---
$routes->get('admin/tools/api-tester-classic', 'Admin\Tools\ApiTesterClassic::index');
$routes->get('admin/tools/api-tester-android', 'Admin\Tools\ApiTesterAndroid::index');


// Add these near the bottom of app/Config/Routes.php (BEFORE any catch-all);
// This switches your main API to the Stable implementation that you just tested.

$routes->get('admin/tools/visits-admin-view', 'Admin\Tools\VisitsAdminView::index');


/**
 * === SAFE ADMIN/TOOLS ROUTES (ADD-ONLY) ===
 * Keep this block at the end of Routes.php
 * It restores /admin and /admin/tools and adds visits-admin-view.
 * All routes use devopenaccess so you can work without auth in DEV.
 */
$routes->group('admin', ['filter' => ['devopenaccess']], static function ($routes) {
    // Land on the Tools menu
    $routes->get('/', 'Admin\Tools\Menu::index');
    $routes->get('tools', 'Admin\Tools\Menu::index');

    // Existing tools (kept here so all are discoverable from /admin/tools)
    $routes->get('tools/migrate', 'Admin\Tools\Migrate::index');
    $routes->get('tools/api-tester', 'Admin\Tools\ApiTester::index');
    $routes->get('tools/api-tester-android', 'Admin\Tools\ApiTesterAndroid::index');
    $routes->get('tools/api-tester-classic', 'Admin\Tools\ApiTesterClassic::index');
    $routes->get('tools/stable-api-tester', 'Admin\Tools\StableApiTester::index');
    $routes->get('tools/db-check', 'Admin\Tools\DbCheck::index');

    // Debug pages that expect a ?key=...; keep their stricter filter if you want
    $routes->get('tools/migrate-debug', 'Admin\Tools\MigrateDebug::index', ['filter' => 'stableapiauth']);
    $routes->get('tools/migrate-debug-step', 'Admin\Tools\MigrateDebugStep::index', ['filter' => 'stableapiauth']);

    // New: Visits Admin View (read-only dashboard that calls your existing API)
    $routes->get('tools/visits-admin-view', 'Admin\Tools\VisitsAdminView::index');
});

// Optional public alias (kept since it appears in your links page)

$routes->get('admin/visit/file', 'Admin\VisitViewController::file', ['filter' => 'devopenaccess']);

// === ADD-ONLY: Tools Today + ensure Visits Admin View present ===
$routes->group('admin', ['filter' => ['devopenaccess']], static function ($routes) {
    $routes->get('tools/today', 'Admin\Tools\Today::index');
    $routes->get('tools/visits-admin-view', 'Admin\Tools\VisitsAdminView::index'); // keep canonical
});


// --- Visual multi-upload tester (dev-open) ---
$routes->group('admin/tools', ['filter' => 'devopen'], static function($routes) {
    $routes->get('upload-tester-multi', 'Admin\Tools\UploadTesterMulti::index');
});
// --- OVERRIDE: Visual multi-upload tester (no filter) ---
$routes->get('admin/tools/upload-tester-multi', 'Admin\Tools\UploadTesterMulti::index');

