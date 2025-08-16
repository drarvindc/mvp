<?php
// Patched Routes.php for admin + API
$routes->group('admin', ['filter' => ['adminauth','admintoolbar','dmydate']], static function($routes) {
    $routes->get('/', 'Admin\Tools\Home::index');
    $routes->get('tools', 'Admin\Tools\Home::index');
    $routes->get('tools/migrate', 'Admin\Tools\Migrate::index');
    $routes->get('tools/api-tester', 'Admin\Tools\ApiTester::index');
    $routes->get('tools/api-tester-android', 'Admin\Tools\ApiTesterAndroid::index');
    $routes->get('tools/api-tester-classic', 'Admin\Tools\ApiTesterClassic::index');
    $routes->get('tools/db-check', 'Admin\Tools\DbCheck::index');
});

$routes->group('api/visit', ['filter' => 'apitoken'], static function($routes) {
    $routes->post('open', 'Api\VisitController::open');
    $routes->post('upload', 'Api\VisitController::upload');
    $routes->post('map-orphans', 'Api\VisitController::mapOrphans');
});
