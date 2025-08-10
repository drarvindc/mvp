<?php
$routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
    $routes->get('diagnostics', 'Admin\DiagnosticsController::index');
    $routes->get('diagnostics/db-check', 'Admin\DiagnosticsController::dbCheck');
    $routes->get('diagnostics/env-check', 'Admin\DiagnosticsController::envCheck');

    $routes->get('db-status', 'Admin\DbStatusController::index');
});
