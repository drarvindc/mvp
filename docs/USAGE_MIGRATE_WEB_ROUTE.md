# Admin-only Web Route for Migrations

## 1) Install files
- Copy `app/Controllers/Admin/MigrateController.php`
- Copy `app/Views/admin/migrate/run.php`
- Copy `app/Filters/AdminAuth.php`
- Copy `app/Config/Routes.snippet.php` (read and paste into your real `app/Config/Routes.php`)

## 2) Configure routes
In `app/Config/Routes.php`:

```php
$routes->group('admin/tools', ['filter' => 'adminauth'], static function($routes){
    $routes->get('migrate', 'Admin\MigrateController::index');
    $routes->post('migrate/run', 'Admin\MigrateController::run');
    $routes->post('migrate/rollback', 'Admin\MigrateController::rollback');
    $routes->post('migrate/seed-species', 'Admin\MigrateController::seedSpecies');
});
```

In `app/Config/Filters.php`:
```php
public $aliases = [
    'adminauth' => \App\Filters\AdminAuth::class,
    // ...
];
```

## 3) Protect access
- Ensure you have admin login sessions, **or**
- Set an env token in `.env`:
  ```
  MIGRATE_WEB_KEY=change-this-to-a-long-random-string
  ```
- Then you can access:
  `https://yourdomain/admin/tools/migrate?key=YOUR_TOKEN`

## 4) Run
- Click **Run Migrations** after each Git pull
- Click **Rollback Last Batch** if needed
- Click **Seed Species & Breeds** on a fresh install

## 5) Example delta migrations
We included two examples (both CI4 and plain SQL):

- Add index on `pets.microchip`  
  - CI4: `database/migrations/20250809_010000_AddMicrochipIndex.php`  
  - SQL: `database/sql/deltas/2025-08-09_add_microchip_index.sql`

- Add `clinic_settings` table  
  - CI4: `database/migrations/20250809_011000_AddClinicSettings.php`  
  - SQL: `database/sql/deltas/2025-08-09_add_clinic_settings.sql`

### How to use delta files (your Option A):
- Open **phpMyAdmin** → Select DB → **Import** one delta at a time in chronological order.
- Always take a quick **Export** backup before applying.

### How to use migration files (your Option B):
- SSH: `php spark migrate`
- No SSH: visit `https://yourdomain/admin/tools/migrate?key=YOUR_TOKEN` and click **Run Migrations**.

## 6) Notes
- Keep `database/sql/initial/*` as your fresh install snapshot.
- Keep `database/sql/deltas/*` in sync with each migration for click-only environments.
- Update `docs/DB_CHANGELOG.md` whenever you add a migration.
