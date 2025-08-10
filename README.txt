Hotfix Package — 2025-08-10T20:54:57.167486

Includes:
- app/Config/Routes.php (optimized groups; public visits-lite; admin/tools/api-tester)
- app/Controllers/Api/VisitController.php (schema-aligned: pets.unique_id, visits, documents; supports date & all=1)
- app/Controllers/VisitsLite.php (public page controller)
- app/Views/visitslite/index.php (fixes $this->request in view; pill UI)
- app/Controllers/Admin/Tools/ApiTester.php + view (restores API tester at /admin/tools/api-tester?key=...)

How to install:
1) Unzip into your project root (merges into app/...). 
2) Visit API tester:
   /index.php/admin/tools/api-tester?key=arvindrchauhan1723
3) Public visits viewer:
   /index.php/visits-lite?uid=250001&date=2025-08-10

Upload endpoint:
POST {site_url('api/visit/upload')}
Fields: uid, [visitId optional], [type optional], file (multipart), [backfill=1 default]
