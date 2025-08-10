Visits-Lite (public) – avoids admin filters
Generated: 2025-08-10T20:36:28.114908

This adds a public, no-auth page at /index.php/visits-lite that renders same-day visits with a pill selector.

Files:
- app/Controllers/VisitsLite.php
- app/Views/visitslite/index.php

Routes:
- Add: $routes->get('visits-lite', 'VisitsLite::index');  (place it NOT inside admin group)

Notes:
- The page calls /index.php/api/visit/today?uid=...&all=1 via fetch().
- If your today() endpoint ignores &date, the page filters client side.
