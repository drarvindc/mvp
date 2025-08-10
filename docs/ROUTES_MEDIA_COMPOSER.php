<?php
// Public routes (not in admin group)
$routes->get('media/ping', 'MediaController::ping');
$routes->get('media/qr-uid', 'MediaController::qrUid');
$routes->get('media/barcode-uid', 'MediaController::barcodeUid');
$routes->get('media/selftest', 'MediaController::selftest'); // JSON check
