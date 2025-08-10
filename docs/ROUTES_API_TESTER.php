<?php
// Add to Routes.php; protect with your adminauth filter
$routes->get('admin/tools/api-tester', 'Admin\ApiTesterController::index', ['filter'=>'adminauth']);
