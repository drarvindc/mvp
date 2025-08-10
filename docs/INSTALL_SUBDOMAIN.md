# Fresh Start Overlay for CodeIgniter 4 (cPanel-friendly)

This package is an **overlay** that you drop on top of a clean CodeIgniter 4 AppStarter.
It includes: admin auth filter, migration UI, DB status UI, idempotent seeds, and full initial schema.

## Step A — Create a clean subdomain that points to /public
1. cPanel → Domains → Create Subdomain, e.g. `vet.yourdomain.com`
2. **Document Root**: set to `/public_html/yourfolder/public` (CI4 public folder)
3. Wait for it to be provisioned (usually instant).

## Step B — Install CodeIgniter 4 (no SSH)
1. Download CI4 AppStarter zip from the official site.
2. cPanel → File Manager → go to the folder **above** `/public` (e.g., `/public_html/yourfolder`).
3. Upload and **Extract** the CI4 zip there. You should see `app/`, `public/`, `system/`, `writable/`, etc.

## Step C — Overlay our package
1. Upload this zip to the same folder and **Extract** (allow MERGE/overwrite).
2. This adds:
   - `app/Filters/AdminAuth.php`, `app/Config/Filters.php`
   - `app/Controllers/Admin/{MigrateController,DbStatusController}.php`
   - `app/Views/admin/{migrate,dbstatus}/*`
   - `app/Database/Seeds/SpeciesSeeder.php`
   - `database/sql/initial/2025-08-10_initial_schema.sql`
   - `database/sql/seed/{001_species.sql,002_breeds.sql}`

## Step D — Configure .env
Copy `.env.example` to `.env`, then edit:
```
CI_ENVIRONMENT = production
app.baseURL = 'https://vet.yourdomain.com/'
MIGRATE_WEB_KEY = change-this-to-a-long-random-secret

database.default.hostname = localhost
database.default.database = cpaneluser_db
database.default.username = cpaneluser_user
database.default.password = your-strong-password
database.default.DBDriver = MySQLi
database.default.charset  = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci
```

## Step E — Add routes (open app/Config/Routes.php)
Append the snippet from `docs/ROUTES_SNIPPET.php`.

## Step F — Run migrations & seed (browser)
Visit:
```
https://vet.yourdomain.com/admin/tools/migrate?key=YOUR_SECRET
```
- Click **Run Migrations**
- Click **Seed Species & Breeds**
- Visit **DB Status**: `/admin/tools/db-status?key=YOUR_SECRET`

If you need to reset, use phpMyAdmin to drop tables or ask me for a reset SQL.

You're ready to build features now.
