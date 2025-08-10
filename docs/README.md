# DB Status Panel

## Installation
1. Copy `app/Controllers/Admin/DbStatusController.php` to your project.
2. Copy `app/Views/admin/dbstatus/index.php` to your project.
3. In `app/Config/Routes.php`, add:
```php
$routes->get('admin/tools/db-status', 'Admin\DbStatusController::index', ['filter' => 'adminauth']);
```
4. Protect with `adminauth` filter or your own admin login.

## Usage
Visit:
```
https://yourdomain/admin/tools/db-status?key=YOUR_TOKEN
```
to see:
- Current time (server TZ Asia/Kolkata)
- Last applied migration batch number
- Total applied migrations
- List of pending migration files

