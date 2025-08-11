Routes + Filters + Migration (Stable API switch) — 2025-08-11T04:22:12.789790

What this does
- Switches your main /api/visit/* routes to the already-working Stable API.
- Adds StableApiAuthFilter (Bearer token) if you don't have it yet.
- Provides a migration to add `mime`, `note`, and optional `pet_id` to `documents` safely.

Install
1) Unzip this into your project root (merges into app/...).
2) Open app/Config/Filters.php and add this alias under $aliases:
   'stableapiauth' => \App\Filters\StableApiAuthFilter::class,
3) Open app/Config/Routes.php and paste the lines from ROUTES_TO_ADD.txt near the bottom (before any catch-alls).
   If you still have old /api/visit/* routes, comment them out so these take precedence.
4) Run migration (CLI or your admin migrate tool):
   php spark migrate -n App
   or via: /index.php/admin/tools/migrate?key=YOURKEY
5) Ensure .env has:
   ANDROID_API_TOKEN="your-long-random-token"

That’s it. Your existing testers can now hit /api/visit/* (Stable API underneath).

Rollback
- You can remove the group from Routes.php and roll back the migration:
  php spark migrate:rollback -n App
