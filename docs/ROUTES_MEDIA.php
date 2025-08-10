<?php
// Add OUTSIDE the admin group (public routes)
$routes->get('media/barcode-uid', 'MediaController::barcodeUid');
$routes->get('media/qr-uid', 'MediaController::qrUid');
$routes->get('media/ping', 'MediaController::ping'); // quick test
