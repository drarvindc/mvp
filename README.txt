Android API Tester (Upload) — restored 2025-08-10T21:13:04.597365

This package DOES NOT overwrite your existing api-tester. It adds a separate page:
/index.php/admin/tools/api-tester-android?key=arvindrchauhan1723

Files:
- app/Controllers/Admin/Tools/ApiTesterAndroid.php
- app/Views/admin/tools/api_tester_android.php
- ROUTES_TO_ADD.txt (single route to paste, outside adminauth)

Features:
- POST /api/visit/open (forceNewVisit supported)
- POST /api/visit/upload (uid, type, file, optional visitId, backfill)
- GET /api/visit/today (uid, date, all=1)

This mirrors the earlier Android-friendly tester with a file picker, preserving your tested flow.
