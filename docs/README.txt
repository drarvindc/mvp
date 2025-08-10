# Composer Media Controller (Final)
Replaces MediaController with Endroid QR (offline) + Picqer Code128.

Routes to add (app/Config/Routes.php):
    $routes->get('media/ping', 'MediaController::ping');
    $routes->get('media/qr-uid', 'MediaController::qrUid');
    $routes->get('media/barcode-uid', 'MediaController::barcodeUid');
    $routes->get('media/selftest', 'MediaController::selftest');

Server (project root):
    composer require endroid/qr-code:^5.0 picqer/php-barcode-generator:^2.4
    composer install --no-dev --optimize-autoloader

Test:
    /index.php/media/selftest
    /index.php/media/qr-uid?uid=250001
    /index.php/media/barcode-uid?uid=250001
