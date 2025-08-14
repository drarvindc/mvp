Admin Landing Fix
=================
Purpose: Prevents 404 on GET /admin by redirecting to /admin/tools.

Steps:
1) Copy app/Controllers/Admin/Home.php into your project.
2) In app/Config/Routes.php, inside your existing 'admin' group, add:
   $routes->get('/', 'Admin\Home::index');
3) Clear cache (writable/cache/*) and retry /index.php/admin after login.
