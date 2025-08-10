# Sample Data (Dummy Families & Pets)

This folder contains idempotent SQL to seed a few **owners**, **mobiles**, and **pets** so you can test:
- Search by **Unique ID** (6 digits: `YY####`, e.g., `250001`).
- Search by **mobile** and see **multiple pets** for a family.

## Files
- `001_dummy_families.sql` — owners, mobiles, pets (safe to re-run).

## How to import
1. Open **phpMyAdmin** → select your database.
2. Go to **Import** → choose `001_dummy_families.sql` and run.
3. Or copy/paste the script into the SQL tab and execute.

Assumptions:
- `species` and some `breeds` are already seeded (use the Species/Breeds seeder first).
- Your schema is from the supplied initial migration.

You can re-run this script safely; it uses `INSERT IGNORE` and `NOT EXISTS` guards.
