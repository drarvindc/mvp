This migration removes any foreign keys or indexes that reference `documents.patient_id`,
since that column does not exist in your schema (you use `pet_id` instead).

Install:
- Unzip into project root.
- Run your migrate tool:
  /index.php/admin/tools/migrate?key=arvindrchauhan1723
