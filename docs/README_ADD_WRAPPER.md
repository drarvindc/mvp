# Initial Schema Migration Wrapper

This CI4 migration class executes all SQL files under `database/sql/initial/`.
Use this if migrations ran but no tables were created.

## Install
1. Copy `app/Database/Migrations/20250810_130500_InitialSchema.php` into your project.
2. Ensure your initial schema SQL exists in `database/sql/initial/`.
3. Run your migration tool (browser or CLI).

