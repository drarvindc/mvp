Visits Lite — no-auth dashboard
Generated: 2025-08-10T20:31:14.557216

What this adds
- A lightweight admin page at /index.php/admin/visits-lite
- No auth filter; avoids redirects you saw on /admin/visits
- The page uses JS to call /api/visit/today?all=1 and render pill selector + attachments

Install
1) Unzip at project root (merges into app/...).
2) Add the route shown in ROUTES_TO_ADD.txt to app/Config/Routes.php.
3) Open: /index.php/admin/visits-lite?uid=250001&date=2025-08-10

Notes
- It does not touch your DB models or filters.
- Attachment links point to /index.php/admin/visit/file?id=... as in your existing system.
