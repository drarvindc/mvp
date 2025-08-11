Admin Login Pack — Sticky Sessions (Generated 2025-08-11T17:54:28)

What this adds
- Session-based admin login with optional "Remember me" (30 days) via a secure cookie.
- AdminAuth filter that guards admin routes, auto-logs via remember cookie, and redirects to /admin/login if needed.
- One-time "MakeAdmin" tool to create/update an admin user by URL key.

Install
1) Unzip into your project root.
2) In app/Config/Filters.php add alias:
   'adminauth' => \App\Filters\AdminAuth::class,

3) In app/Config/Routes.php add:
   $routes->get('admin/login', 'Admin\Auth\Login::index');
   $routes->post('admin/login', 'Admin\Auth\Login::attempt');
   $routes->get('admin/logout', 'Admin\Auth\Login::logout');
   $routes->get('admin/logout-all', 'Admin\Auth\Login::logoutAll');
   $routes->get('admin/tools/make-admin', 'Admin\Tools\MakeAdmin::index');

   // Then wrap your admin routes:
   $routes->group('admin', ['filter'=>'adminauth'], static function($routes) {
       // your admin routes here
   });

4) In .env (for long-lived sessions), set:
   app.sessionDriver = 'CodeIgniter\Session\Handlers\FileHandler'
   app.sessionCookieName = 'ci_session'
   app.sessionSavePath = 'writable/session'
   app.sessionExpiration = 2592000
   app.sessionMatchIP = false
   app.sessionTimeToUpdate = 300
   app.sessionRegenerateDestroy = false

5) Create your first admin user (then remove this route if you want):
   /index.php/admin/tools/make-admin?key=arvindrchauhan1723&email=you@example.com&password=Secret123&name=Dr+Name&role=admin

6) Log in:
   /index.php/admin/login   (keep "Remember me" checked)

Notes
- "Log out everywhere" is available at /index.php/admin/logout-all (revokes remember tokens).
- File links like /index.php/admin/visit/file?id=... will work from Visits-Lite as long as your browser is logged in.
