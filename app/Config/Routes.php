<?php
// PUBLIC (no auth): patient/intake, visits-lite, tools/visits-admin-view


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
$routes->group('admin', ['filter'=>'adminauth,admintoolbar,dmydate'], static function($routes) {
    // Admin landing (GET /admin) -> redirect to /admin/tools (which then redirects to migrate)
    $routes->get('/', 'Admin\Home::index');

    // Convenience: /admin/tools -> /admin/tools/migrate
    $routes->get('tools', static function () {
        return redirect()->to(site_url('admin/tools/migrate'));
    });

    // (keep your other admin pages mapped elsewhere or add here as needed)
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


$routes->get('admin/login',  'Admin\Auth\Login::index');

$routes->post('api/visit/map-orphans', 'Stable\VisitController::mapOrphans');


<?php
// ===============================
// APPEND-ONLY: Admin tool routes — uses dev bypass in dev
// ===============================
$routes->get('admin', 'Admin\\Tools\\Home::index', ['filter' => 'devopenaccess']);
$routes->get('admin/tools', 'Admin\\Tools\\Home::index', ['filter' => 'devopenaccess']);

$routes->get('admin/tools/migrate', 'Admin\\Tools\\MigrateDebug::index', ['filter' => 'devopenaccess']);
$routes->get('admin/tools/migrate-debug', 'Admin\\Tools\\MigrateDebug::index', ['filter' => 'devopenaccess']);
$routes->get('admin/tools/migrate-debug-step', 'Admin\\Tools\\MigrateDebugStep::index', ['filter' => 'devopenaccess']);

$routes->get('admin/tools/api-tester', 'Admin\\Tools\\ApiTester::index', ['filter' => 'devopenaccess']);
$routes->get('admin/tools/api-tester-android', 'Admin\\Tools\\ApiTesterAndroid::index', ['filter' => 'devopenaccess']);
$routes->get('admin/tools/api-tester-classic', 'Admin\\Tools\\ApiTesterClassic::index', ['filter' => 'devopenaccess']);
$routes->get('admin/tools/stable-api-tester', 'Admin\\Tools\\StableApiTester::index', ['filter' => 'devopenaccess']);

// Optional: only if you later add DbCheck controller
// $routes->get('admin/tools/db-check', 'Admin\\Tools\\DbCheck::index', ['filter' => 'devopenaccess']);

// ===============================
// APPEND-ONLY: API — dev bypass injects token
// ===============================
$routes->group('api/visit', ['filter' => 'devopenaccess'], static function($routes) {
    $routes->post('open', 'Api\\VisitController::open');
    $routes->post('upload', 'Api\\VisitController::upload');
    $routes->post('map-orphans', 'Api\\VisitController::mapOrphans');
});
