# Barcode + QR Patch

Adds server-side PNG generators:
- GET /media/barcode-uid?uid=YY#### (Code128, PNG)
- GET /media/qr-uid?uid=... (QR, PNG)

Also updates the provisional print view to embed both images.

## Install
1) Merge into your CI4 project root.
2) Add routes from docs/ROUTES_MEDIA.php to app/Config/Routes.php.
3) Open /patient/provisional or /patient/print-existing to see barcode+QR.

Note: The QR fallback uses Google Chart API; if your server blocks outbound requests, replace with a local phpqrcode library (I can ship it next).
