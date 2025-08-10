-- 001_dummy_families.sql
SET NAMES utf8mb4;
SET time_zone = '+05:30';

-- Ensure some common breeds exist (safe if already present)
INSERT IGNORE INTO breeds (species_id, name)
SELECT s.id, 'Labrador Retriever' FROM species s WHERE s.name='Canine';
INSERT IGNORE INTO breeds (species_id, name)
SELECT s.id, 'German Shepherd' FROM species s WHERE s.name='Canine';
INSERT IGNORE INTO breeds (species_id, name)
SELECT s.id, 'Persian' FROM species s WHERE s.name='Feline';
INSERT IGNORE INTO breeds (species_id, name)
SELECT s.id, 'Siamese' FROM species s WHERE s.name='Feline';

-- Make sure 2025 counter won't collide with our UIDs
INSERT INTO year_counters (year_two, last_seq, updated_at)
VALUES ('25', 3, NOW())
ON DUPLICATE KEY UPDATE last_seq = GREATEST(last_seq, 3), updated_at = NOW();

/* ---------------------------------------------------------
   Family 1: Ravi Sharma (two mobiles), two pets (dog + cat)
--------------------------------------------------------- */
-- Owner (insert if not exists)
INSERT INTO owners (first_name, middle_name, last_name, email, locality, address, status, created_at, updated_at)
SELECT 'Ravi', NULL, 'Sharma', NULL, 'Andheri', 'Mumbai', 'active', NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM owners WHERE first_name='Ravi' AND last_name='Sharma' AND status='active'
);
-- Capture owner id
SET @owner1 := (SELECT id FROM owners WHERE first_name='Ravi' AND last_name='Sharma' ORDER BY id DESC LIMIT 1);

-- Mobiles
INSERT IGNORE INTO owner_mobiles (owner_id, mobile_e164, is_primary, is_verified, created_at)
VALUES (@owner1, '9876543210', 1, 1, NOW());
INSERT IGNORE INTO owner_mobiles (owner_id, mobile_e164, is_primary, is_verified, created_at)
VALUES (@owner1, '9123456780', 0, 0, NOW());

-- Look up species/breeds
SET @canine := (SELECT id FROM species WHERE name='Canine' LIMIT 1);
SET @feline := (SELECT id FROM species WHERE name='Feline' LIMIT 1);
SET @lab    := (SELECT b.id FROM breeds b WHERE b.name='Labrador Retriever' AND b.species_id=@canine LIMIT 1);
SET @pers   := (SELECT b.id FROM breeds b WHERE b.name='Persian' AND b.species_id=@feline LIMIT 1);

-- Pets (Unique IDs 250001 and 250002)
INSERT INTO pets (owner_id, unique_id, pet_name, species_id, breed_id, gender, status, created_at, updated_at)
SELECT @owner1, '250001', 'Bruno', @canine, @lab, 'male', 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM pets WHERE unique_id='250001');

INSERT INTO pets (owner_id, unique_id, pet_name, species_id, breed_id, gender, status, created_at, updated_at)
SELECT @owner1, '250002', 'Misty', @feline, @pers, 'female', 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM pets WHERE unique_id='250002');

/* ---------------------------------------------------------
   Family 2: Meera Patel (single mobile), one pet (cat)
--------------------------------------------------------- */
INSERT INTO owners (first_name, middle_name, last_name, email, locality, address, status, created_at, updated_at)
SELECT 'Meera', NULL, 'Patel', NULL, 'Vastrapur', 'Ahmedabad', 'active', NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM owners WHERE first_name='Meera' AND last_name='Patel' AND status='active'
);
SET @owner2 := (SELECT id FROM owners WHERE first_name='Meera' AND last_name='Patel' ORDER BY id DESC LIMIT 1);

INSERT IGNORE INTO owner_mobiles (owner_id, mobile_e164, is_primary, is_verified, created_at)
VALUES (@owner2, '9988776655', 1, 1, NOW());

SET @feline := (SELECT id FROM species WHERE name='Feline' LIMIT 1);
SET @siam   := (SELECT b.id FROM breeds b WHERE b.name='Siamese' AND b.species_id=@feline LIMIT 1);

INSERT INTO pets (owner_id, unique_id, pet_name, species_id, breed_id, gender, status, created_at, updated_at)
SELECT @owner2, '250003', 'Nala', @feline, @siam, 'female', 'active', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM pets WHERE unique_id='250003');

/* ---------------------------------------------------------
   Family 3: Provisional owner + provisional pet (no mobiles yet)
--------------------------------------------------------- */
INSERT INTO owners (first_name, middle_name, last_name, email, locality, address, status, created_at, updated_at)
SELECT '', NULL, '', NULL, NULL, NULL, 'provisional', NOW(), NOW()
WHERE NOT EXISTS (
  SELECT 1 FROM owners WHERE status='provisional' AND first_name='' AND last_name='' LIMIT 1
);
SET @owner3 := (SELECT id FROM owners WHERE status='provisional' AND first_name='' AND last_name='' ORDER BY id DESC LIMIT 1);

SET @canine := (SELECT id FROM species WHERE name='Canine' LIMIT 1);

INSERT INTO pets (owner_id, unique_id, pet_name, species_id, breed_id, gender, status, created_at, updated_at)
SELECT @owner3, '250004', NULL, @canine, NULL, 'unknown', 'provisional', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM pets WHERE unique_id='250004');

-- Done
SELECT 'Dummy families seeded' AS status;
