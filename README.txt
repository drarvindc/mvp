Vet Clinic — Force New Visit package
Generated: 2025-08-10T20:04:23.584317

What this adds
- Support for creating multiple visits on the same date via /api/visit/open with forceNewVisit=1
- Safer sequencing with DB transaction + FOR UPDATE
- /api/visit/today supports ?all=1 to list all same-day visits including attachments
- Attachment upload filenames include -v{sequence} when sequence > 1

Install
1) Unzip into your project root so the 'app' directory merges.
2) Run migration:
   php spark migrate -n App

Routes to add
See ROUTES_TO_ADD.txt (you only need to paste the three lines).

Notes
- Assumes you already have PatientModel with 'uid' field; and 'attachments' table + admin/visit/file handler.
- Storage path uses WRITEPATH/patients/YYYY/UID.
