-- 2025-08-10_initial_schema.sql (v2, FK-consistent)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE owners (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  first_name VARCHAR(60) NOT NULL,
  middle_name VARCHAR(60) NULL,
  last_name VARCHAR(60) NOT NULL,
  email VARCHAR(120) NULL,
  locality VARCHAR(120) NULL,
  address VARCHAR(255) NULL,
  status ENUM('active','provisional','merged') DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE owner_mobiles (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  owner_id BIGINT UNSIGNED NOT NULL,
  mobile_e164 VARCHAR(20) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  UNIQUE KEY uq_owner_mobile (owner_id, mobile_e164),
  KEY idx_mobile (mobile_e164),
  CONSTRAINT fk_owner_mobiles_owner FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE species (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE breeds (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  species_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL,
  UNIQUE KEY uq_species_breed (species_id, name),
  CONSTRAINT fk_breeds_species FOREIGN KEY (species_id) REFERENCES species(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pets (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  owner_id BIGINT UNSIGNED NOT NULL,
  unique_id CHAR(6) NOT NULL UNIQUE,
  pet_name VARCHAR(80) NULL,
  species_id BIGINT UNSIGNED NULL,
  breed_id BIGINT UNSIGNED NULL,
  gender ENUM('male','female','unknown') DEFAULT 'unknown',
  dob DATE NULL,
  age_years TINYINT UNSIGNED NULL,
  age_months TINYINT UNSIGNED NULL,
  color VARCHAR(60) NULL,
  microchip VARCHAR(32) NULL,
  notes TEXT NULL,
  status ENUM('active','provisional','archived','merged') DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY idx_pets_owner (owner_id),
  CONSTRAINT fk_pets_owner FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE,
  CONSTRAINT fk_pets_species FOREIGN KEY (species_id) REFERENCES species(id) ON DELETE SET NULL,
  CONSTRAINT fk_pets_breed FOREIGN KEY (breed_id) REFERENCES breeds(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE year_counters (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  year_two CHAR(2) NOT NULL UNIQUE,
  last_seq INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE visit_seq_counters (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  pet_id BIGINT UNSIGNED NOT NULL UNIQUE,
  last_visit_seq INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_visit_seq_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE visits (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  pet_id BIGINT UNSIGNED NOT NULL,
  visit_date DATE NOT NULL,
  visit_seq INT UNSIGNED NOT NULL,
  status ENUM('open','closed') DEFAULT 'open',
  source ENUM('android','web','pos-only','ingest','email','whatsapp') DEFAULT 'web',
  reason VARCHAR(200) NULL,
  remarks TEXT NULL,
  next_visit DATE NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_visit (pet_id, visit_date, visit_seq),
  CONSTRAINT fk_visits_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documents (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  patient_unique_id CHAR(6) NOT NULL,
  pet_id BIGINT UNSIGNED NOT NULL,
  visit_id BIGINT UNSIGNED NULL,
  type ENUM('photo','prescription','doc','xray','lab','usg','invoice','vaccine','deworm','tick','consent','referral','qrcode','barcode','medscan') NOT NULL,
  subtype VARCHAR(60) NULL,
  path VARCHAR(255) NOT NULL,
  filename VARCHAR(180) NOT NULL,
  source ENUM('android','web','pos','ingest','email','whatsapp') NOT NULL,
  ref_id VARCHAR(60) NULL,
  seq SMALLINT UNSIGNED NULL,
  mime VARCHAR(80) NULL,
  size_bytes INT UNSIGNED NULL,
  captured_at DATETIME NOT NULL,
  checksum_sha1 CHAR(40) NULL,
  created_at DATETIME NOT NULL,
  KEY idx_docs_pet_date (pet_id, captured_at),
  KEY idx_docs_visit (visit_id),
  KEY idx_docs_type_date (type, captured_at),
  KEY idx_docs_ref (ref_id),
  KEY idx_docs_unique_id (patient_unique_id),
  CONSTRAINT fk_docs_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  CONSTRAINT fk_docs_visit FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE preventive_templates (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  species_id BIGINT UNSIGNED NULL,
  type ENUM('vaccine','deworm','tickflea') NOT NULL,
  subtype VARCHAR(80) NULL,
  json_rules JSON NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT fk_pt_species FOREIGN KEY (species_id) REFERENCES species(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE preventive_plans (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  pet_id BIGINT UNSIGNED NOT NULL,
  type ENUM('vaccine','deworm','tickflea') NOT NULL,
  status ENUM('active','paused') DEFAULT 'active',
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_pp_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE preventive_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  plan_id BIGINT UNSIGNED NOT NULL,
  type ENUM('vaccine','deworm','tickflea') NOT NULL,
  subtype VARCHAR(80) NULL,
  due_date DATE NOT NULL,
  window_start DATE NULL,
  window_end DATE NULL,
  status ENUM('scheduled','overdue','done','skipped') DEFAULT 'scheduled',
  reminder_state ENUM('none','pending','sent','confirmed') DEFAULT 'pending',
  last_reminder_at DATETIME NULL,
  visit_id BIGINT UNSIGNED NULL,
  notes VARCHAR(180) NULL,
  created_at DATETIME NOT NULL,
  KEY idx_pi_due (due_date, status),
  CONSTRAINT fk_pi_plan FOREIGN KEY (plan_id) REFERENCES preventive_plans(id) ON DELETE CASCADE,
  CONSTRAINT fk_pi_visit FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE preventive_events (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  item_id BIGINT UNSIGNED NULL,
  pet_id BIGINT UNSIGNED NOT NULL,
  date_given DATE NOT NULL,
  visit_id BIGINT UNSIGNED NULL,
  captured_by ENUM('android','web','pos','ingest','email','whatsapp') NOT NULL,
  dose_ml DECIMAL(5,2) NULL,
  route VARCHAR(20) NULL,
  site VARCHAR(40) NULL,
  manufacturer VARCHAR(80) NULL,
  batch VARCHAR(40) NULL,
  expiry DATE NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_pe_item FOREIGN KEY (item_id) REFERENCES preventive_items(id) ON DELETE SET NULL,
  CONSTRAINT fk_pe_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  CONSTRAINT fk_pe_visit FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pos_invoices (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  pet_id BIGINT UNSIGNED NOT NULL,
  visit_id BIGINT UNSIGNED NULL,
  unique_id CHAR(6) NOT NULL,
  invoice_id VARCHAR(40) NOT NULL UNIQUE,
  sale_datetime DATETIME NOT NULL,
  total_amount DECIMAL(10,2) NULL,
  source ENUM('webhook','poll') NOT NULL,
  created_at DATETIME NOT NULL,
  KEY idx_pos_unique (unique_id),
  CONSTRAINT fk_pos_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  CONSTRAINT fk_pos_visit FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pos_invoice_items (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  invoice_db_id BIGINT UNSIGNED NOT NULL,
  product_code VARCHAR(60) NULL,
  product_name VARCHAR(120) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  unit_price DECIMAL(10,2) NULL,
  CONSTRAINT fk_pos_item_invoice FOREIGN KEY (invoice_db_id) REFERENCES pos_invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reminders (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  pet_id BIGINT UNSIGNED NOT NULL,
  owner_id BIGINT UNSIGNED NOT NULL,
  type ENUM('visit','vaccine','deworm','tickflea','custom') NOT NULL,
  subtype VARCHAR(60) NULL,
  due_date DATE NOT NULL,
  status ENUM('pending','sent','snoozed','skipped','failed') DEFAULT 'pending',
  channel ENUM('whatsapp','sms','both') DEFAULT 'whatsapp',
  last_attempt_at DATETIME NULL,
  attempts_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  KEY idx_rem_due (due_date, status),
  CONSTRAINT fk_rem_pet FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
  CONSTRAINT fk_rem_owner FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cert_templates (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  category ENUM('certificate','report','letter') NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  html LONGTEXT NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cert_generated (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  template_id BIGINT UNSIGNED NOT NULL,
  pet_id BIGINT UNSIGNED NOT NULL,
  visit_id BIGINT UNSIGNED NULL,
  path VARCHAR(255) NOT NULL,
  filename VARCHAR(160) NOT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_cg_template FOREIGN KEY (template_id) REFERENCES cert_templates(id) ON DELETE RESTRICT,
  CONSTRAINT fk_cg_pet FOREIGN KEY (pet_id) REFERENCES pets(id),
  CONSTRAINT fk_cg_visit FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('frontdesk','doctor','admin') NOT NULL DEFAULT 'frontdesk',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_tokens (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  token VARCHAR(255) NOT NULL,
  last_used_at DATETIME NULL,
  revoked TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_api_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
