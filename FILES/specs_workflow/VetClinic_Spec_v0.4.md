# Veterinary Clinic Management System — Agreed Specs & Workflow (v0.4)

**Last updated:** 2025-08-10  IST  
**Owner:** Dr. Arvind (project lead) · **Assistant:** GPT-5 Thinking  
**Stack Target:** cPanel/DirectAdmin compatible · PHP 8.1+ · MySQL/MariaDB · CodeIgniter 4 + AdminLTE · Composer

---

## 0) Snapshot / Status

### Implemented (server: mvp.dpharma.in)
- **Intake flow** (`/patient/intake`) – search by **UID** (6 digits) or **mobile** (10 digits).  
- **Multi-pet family view** – for UID: shows selected pet + siblings; for mobile: shows all pets tied to that family.  
- **Provisional creation** – generates **Unique ID** (`YY####`) via `year_counters`, creates provisional owner + pet, opens **printable letterhead**.  
- **Barcode & QR on letterhead** – server-side PNG endpoints:  
  - `/media/barcode-uid?uid=YY####` (Picqer Code128, offline)  
  - `/media/qr-uid?uid=…` (Endroid v5, offline)  
- **Diagnostics** – `admin/tools/diagnostics`, `db-status` (admin-auth protected).  
- **Sample data** – dummy families (Bruno/Misty etc.) and working search.

### Verified libs (Composer)
- `endroid/qr-code` **5.1.0** ✅  
- `picqer/php-barcode-generator` **^2.4** ✅  
- (Planned) `dompdf/dompdf` **^2.0** ⏳

---

## 1) Tech Stack & Deployment

- **Hosting:** cPanel/DirectAdmin or VPS (managed).  
- **Language/Framework:** PHP **8.1+**, **CodeIgniter 4** (CI4).  
- **DB:** MySQL/MariaDB (InnoDB, utf8mb4).  
- **UI:** AdminLTE 3.x (Bootstrap 4) for admin/back-office; simple top-nav views for patient-facing pages now.  
- **Composer packages:**  
  - QR: `endroid/qr-code:^5`  
  - Barcode: `picqer/php-barcode-generator:^2.4`  
  - PDF (planned): `dompdf/dompdf:^2.0`  
- **Deploy flow:** GitHub → cPanel Git Deploy → `composer install --no-dev --optimize-autoloader` in **project root** (parent of `public/`).  
- **Environment:** `.env` with DB creds and secrets; temporary web-key for admin tools; CSRF on forms.

---

## 2) IDs, Data Model & File Storage

### Unique ID (UID)
- **Format:** `YY####` (last two digits of current year + 4-digit sequence starting `0001`).  
- **Source of truth:** table `year_counters (year_two, last_seq)` used transactionally during provisional creation.  
- **Barcode/QR:** Letterhead prints both linked to the UID (to drive Android scanning).

### Family / Ownership
- **One Owner per family** (primary person).  
- **Multiple mobiles per owner** (e.g., family members).  
- **Multiple pets per owner**.  
- **Search rules:**
  - **UID:** exact 6 digits → show that pet and **all siblings** (same owner).  
  - **Mobile (10 digits):** show **all pets** tied to any owner with that mobile.

### Storage & Filenames (Confirmed)
- **Folder layout:** `/storage/patients/{YYYY}/{UNIQUE_ID}/`  
- **Filename convention:** `DDMMYY-TYPE-UNIQUEID(-NN).ext`  
  - Examples: `081025-rx-250001.jpg`, `081025-xray-250001-02.png`, `081025-usg-250123.pdf`  
- **Types (extensible):** `rx`, `xray`, `lab`, `usg`, `doc`, `photo`, `invoice`.  
- **Why year partition:** improves filesystem performance with many patients.

---

## 3) Patient Intake & Provisional Workflow (Final)

**Entry point:** `/patient/intake`  
- The input gets focus on load. Barcode/QR scanners can type directly.  
- **If 6 digits** → treat as **UID**. Otherwise treat as **mobile**.  

**Search outcomes:**
1. **UID hit:** show matched pet **highlighted** + list **all pets** for the same owner.  
   - Button: **Print Letterhead** (pre-fills UID + pet/owner if available).  
2. **Mobile hit:** list **all pets** for that owner (multi-pet).  
   - Each card → **Print Letterhead** for selected pet.  
3. **No match:** show _“No match found”_ → **Create Provisional**.  
   - On submit (mobile required), system:  
     - Reserves next **UID** (`YY####`) and creates **provisional owner** (if needed) and **provisional pet**.  
     - Opens **printable letterhead** with UID, barcode, and QR.  
   - **De-dup policy:** no auto-merge. Admin reconciles later for speed/safety.

**Letterhead (printable):**
- Shows **Date**, **UID**, **Barcode**, **QR**, and blank fields for pet/owner/age/notes.  
- Purpose: doctor writes all findings and prescription on this sheet.

---

## 4) Visit Capture & Daily Record

**Capture hubs:**
1) **Android admin app (primary)** – scan UID (barcode/QR) → upload **photos** of patient, prescription, and docs; scan medicine barcodes (later).  
2) **Clinic PC** – upload from scanner/camera attached to PC (web UI).  
3) **Network folder ingestion (later)** – poll a network share for files named `{UID}-{DATE}`; auto-import to that patient.  
4) **POS integration (OSPOS)** – on invoice complete, push dispensed medicines to visit.

**Visit model (lightweight):**
- **Auto-create visit** on first action **per UID per day** (upload or POS).  
- Allow **multiple visits per day** (edge case: morning & evening) → explicit **“New Visit (same day)”** control.  
- Attachments: photos, PDFs under patient’s year/UID folder with convention above.  
- **Late-arriving reports:** allow uploads by UID anytime; if none today, create a **“late-report”** visit tied to the report date.

**Status (optional, backlog):** `pending_print`, `open`, `closed` for analytics — to be finalized later.

---

## 5) Preventive Care: Vaccination / Deworm / Tick-Flea

### Goals
- Generate **tentative schedules** (template-based), set **reminders**, record **actual events** during visits, then generate **next due** dates (booster/annual).

### Data ideas (to refine)
- `preventive_items` master: name, category (`vax|deworm|ectoparasite`), brand/form, species scope.  
- `preventive_plans` template per species/age → dose offsets/booster logic.  
- `preventive_events` per pet: item, date, dose, lot, next_due, notes, linked visit.  
- Reminder engine computes upcoming **next_due** list.

### Reminders
- **Channels:** WhatsApp + SMS (gateway configurable), with a **daily calling sheet** (X days ahead configurable).  
- **Nags:** 7d/3d/1d pre-due; 1d post-miss; weekly for 3 weeks — configurable.  
- Opt-out flags per owner and per pet.

*(We’ll finalize the exact schema and screens when we implement this module.)*

---

## 6) Certificates & Templates

- **Token-based templates** with live preview and print-to-PDF (dompdf).  
- Examples: **Fitness**, **Travel**, **Vaccination status**, **Death** certificate.  
- **Tokens:** `{{pet.name}}`, `{{pet.unique_id}}`, `{{owner.name}}`, `{{species}}`, `{{breed}}`, `{{age.human}}`, `{{today}}`, `{{doctor.name}}`, `{{clinic.name}}` etc.  
- **Manage Templates** page: list → select → edit WYSIWYG/HTML → save → test with a patient.  
- Output stored under patient folder using standard naming.

---

## 7) POS Sync (OSPOS)

**Scenarios:**
1) Visit + billing: medicines scanned & billed in OSPOS.  
2) Counter-only sale: OSPOS only, but link by **UID** or **mobile** to fetch pet/owner display.

**Sync approach:**
- Preferred: **Webhook from OSPOS** on `sale_complete` → POST to our endpoint with `{invoice_id, uid/mobile, items[]}`.  
- Fallback: **Poller** (cron) to fetch completed sales by time window and match.  
- On receipt, create (or append to) **today’s visit** for that UID; store `{invoice_id, items, qty}` and **link** to the OSPOS invoice.

---

## 8) Admin Tools & Migrations

- **DB Status:** `admin/tools/db-status` – last batch, applied count, pending files.  
- **Diagnostics:** `admin/tools/diagnostics` – env + DB overview.  
- **Web migrations** (admin-only, CSRF + secret key).  
- **Migrations + SQL snapshots**: CI4 migrations = source of truth; SQL dumps for bootstrap and safety.
- **Sample data:** `database/sql/sample/001_dummy_families.sql` etc.

---

## 9) Security & Access

- **Short term:** Admin key filter for tools; CSRF on forms.  
- **Near term:** Real user auth for admin, role/permission matrix.  
- **Android app auth:** key-based or token with rotation.  
- **PPI:** Avoid storing excessive personal data; log access to files.

---

## 10) API (Android, first cut)

- **Open by UID:** returns pet/owner basics + today’s visit (or creates stub).  
- **Upload endpoints:** `POST /api/visit/upload` with `uid`, `type`, file (image/pdf); server stores using standard path + filename; returns attachment ID.  
- **Scan medicine (later):** capture barcode → enrich via OSPOS.

*(We’ll spec concrete routes & payloads before implementation.)*

---

## 11) Screens (current & planned)

- **Patient Intake:** `/patient/intake`, `/patient/find`, `/patient/print-existing`, `/patient/provisional/create`.  
- **Admin Tools:** `/admin/tools/diagnostics`, `/admin/tools/db-status`, `/admin/tools/migrate` (if present).  
- **Visit Dashboard (planned):** per-day visit with attachments, POS items, reminders, next visit.

---

## 12) Edge Cases (covered)

- **Two separate encounters same day:** allow manual **New Visit (same day)**.  
- **Midnight spillover:** late entries allowed; attach to correct date or mark as late-report.  
- **Provisional patients:** created fast; admin completes details later.  
- **Offline Android:** queue uploads; clinic PC as fallback.  
- **De-dup:** never auto-merge; admin reconcile tool (backlog).

---

## 13) Backlog / Nice-to-haves

- **Full Visit Dashboard** with timeline and actions.  
- **Vaccination/Deworm/Tick** module screens + templates.  
- **Certificate designer** and token testing tool.  
- **Network-folder ingestion** daemon/UI.  
- **Owner merge tool** (de-dup).  
- **WhatsApp/SMS gateway integration** and scheduler.  
- **OSPOS webhook/poller** configuration UI.  
- **Audit logs** for view/download of documents.  
- **Exports/Reports** module.

---

## 14) Changelog (highlights)

- **2025-08-10:**  
  - Finalized intake + provisional; UID scheme; family model; storage pattern.  
  - Implemented server: intake, multi-pet view, provisional creation, letterhead, barcode+QR (offline).  
  - Fixed mobile search to list siblings; fixed Endroid v5 QR; confirmed sample data.

---

## 15) Quick Tests

- `/patient/intake` → `9876543210` → **Bruno + Misty** visible.  
- `/patient/intake` → `250001` → shows both with **Bruno** highlighted.  
- `/media/barcode-uid?uid=250001` → PNG OK.  
- `/media/qr-uid?uid=250001` → PNG OK.  
- Print letterhead → both codes visible.

---

## 16) Next Up (proposal)

1) **Android upload API** (UID open + file upload); simple web page to view visit attachments.  
2) **POS sync stub** (endpoint + mock receiver).  
3) **Certificate manager** (template CRUD + token test).  
4) **Preventive engine** (schema + first UI).

---

**End of spec.**
