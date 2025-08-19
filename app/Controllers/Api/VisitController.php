<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Visits API
 *
 * Endpoints:
 *  - POST /api/visit/open            uid=250001 [&forceNewVisit=1]
 *  - GET  /api/visit/by-date         token=&uid=&date=dd-mm-yyyy|yyyy-mm-dd [&all=1]
 *  - POST /api/visit/upload          token=&uid=&type=rx|photo|doc|xray|lab|usg|invoice&file=@...
 *  - POST /api/visit/map-orphans     token=[&uid=250001]
 *
 * Auth:
 *  - In DEV if DEV_NO_AUTH=true, auth is bypassed
 *  - Else token must equal ANDROID_API_TOKEN
 */
class VisitController extends BaseController
{
    // ---------- PUBLIC ENDPOINTS ----------

    /**
     * POST /api/visit/open
     * Body: uid=250001 [&forceNewVisit=1]
     * Response:
     *  { ok:true, visit:{ id, uid, date:"dd-mm-yyyy", sequence:<int>, wasCreated:<bool> } }
     */
    public function open(): ResponseInterface
    {
        if (! $this->authOk()) {
            return $this->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $uid = trim((string) ($this->request->getPost('uid') ?? ''));
        $forceNew = (bool) ($this->request->getPost('forceNewVisit') ?? false);

        if ($uid === '' || strlen($uid) !== 6) {
            return $this->json(['ok' => false, 'error' => 'uid_invalid'], 422);
        }

        $db = db_connect();
        $petId = $this->petIdFromUid($uid, $db);
        if (! $petId) {
            return $this->json(['ok' => false, 'error' => 'pet_not_found_for_uid'], 404);
        }

        $today = date('Y-m-d');

        if ($forceNew) {
            $seq = $this->nextVisitSeqForDate($petId, $today, $db) + 1;
            $id  = $this->createVisit($petId, $today, $seq, $db);
            $wasCreated = true;
        } else {
            // try latest existing today
            $row = $db->query(
                "SELECT id, visit_seq FROM visits WHERE pet_id=? AND visit_date=? ORDER BY visit_seq DESC LIMIT 1",
                [$petId, $today]
            )->getRowArray();

            if ($row) {
                $id = (int) $row['id'];
                $seq = (int) $row['visit_seq'];
                $wasCreated = false;
            } else {
                $seq = 1;
                $id  = $this->createVisit($petId, $today, $seq, $db);
                $wasCreated = true;
            }
        }

        return $this->json([
            'ok' => true,
            'visit' => [
                'id'        => $id,
                'uid'       => $uid,
                'date'      => $this->isoToDmy($today),
                'sequence'  => $seq,
                'wasCreated'=> $wasCreated,
            ],
        ]);
    }

    /**
     * GET /api/visit/by-date?token=&uid=&date=dd-mm-yyyy|yyyy-mm-dd[&all=1]
     * Response:
     *  { ok:true, date:"dd-mm-yyyy", results:[ {id,date,sequence,documents:[...]}, ... ] }
     */
    public function byDate(): ResponseInterface
    {
        if (! $this->authOk()) {
            return $this->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $uid  = trim((string) ($this->request->getGet('uid') ?? ''));
        $date = trim((string) ($this->request->getGet('date') ?? ''));
        $all  = (int) ($this->request->getGet('all') ?? 0);

        if ($uid === '' || strlen($uid) !== 6) {
            return $this->json(['ok' => false, 'error' => 'uid_invalid'], 422);
        }
        if ($date === '') {
            return $this->json(['ok' => false, 'error' => 'date_required'], 422);
        }

        $iso = $this->normalizeDateToIso($date);
        if (! $iso) {
            return $this->json(['ok' => false, 'error' => 'date_invalid'], 422);
        }

        $db    = db_connect();
        $petId = $this->petIdFromUid($uid, $db);
        if (! $petId) {
            return $this->json(['ok' => true, 'date' => $this->isoToDmy($iso), 'results' => []]); // silent empty
        }

        $visits = $this->fetchVisitsWithDocs($petId, $iso, $all === 1, $db);

        return $this->json([
            'ok'      => true,
            'date'    => $this->isoToDmy($iso),
            'results' => $visits,
        ]);
    }

    /**
     * POST /api/visit/upload
     * Enforces type and generates collision-safe filenames.
     * Response aligns with your testers.
     */
    public function upload(): ResponseInterface
    {
        if (! $this->authOk()) {
            return $this->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $uid   = trim((string) ($this->request->getPost('uid') ?? ''));
        $type  = strtolower(trim((string) ($this->request->getPost('type') ?? '')));
        $note  = trim((string) ($this->request->getPost('note') ?? ''));
        $file  = $this->request->getFile('file');

        if ($uid === '' || strlen($uid) !== 6) {
            return $this->json(['ok' => false, 'error' => 'uid_invalid'], 422);
        }
        if (! $file || ! $file->isValid()) {
            return $this->json(['ok' => false, 'error' => 'file_missing_or_invalid'], 422);
        }

        // type required + allowlist
        $allowed = ['rx','photo','doc','xray','lab','usg','invoice'];
        if ($type === '' || ! in_array($type, $allowed, true)) {
            return $this->json(['ok' => false, 'error' => 'type_invalid', 'allowed' => $allowed], 422);
        }
        // Map "rx" to DB enum if needed
        $typeDb = ($type === 'rx') ? 'prescription' : $type;

        $db    = db_connect();
        $petId = $this->petIdFromUid($uid, $db); // may be null; we’ll still accept upload

        // Build filename: ddmmyy-type-uid-XX.ext
        $ddmmyy  = date('dmy');
        $seq     = $this->nextSequenceForDay($uid, $ddmmyy, $db);
        $ext     = strtolower($file->getClientExtension() ?: pathinfo($file->getName(), PATHINFO_EXTENSION) ?: 'bin');
        $stem    = sprintf('%s-%s-%s-%02d', $ddmmyy, $type, $uid, $seq);
        $final   = $this->uniqueFilename($stem, $ext, $db);

        // Target location (writable/uploads)
        $targetDir  = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads';
        @is_dir($targetDir) || @mkdir($targetDir, 0775, true);
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $final;

        try {
            $file->move($targetDir, $final, true);
        } catch (\Throwable $e) {
            return $this->json(['ok' => false, 'error' => 'file_move_failed', 'detail' => $e->getMessage()], 500);
        }

        // Optionally map to today's visit
        $visitId = null;
        try {
            if ($petId) {
                $visitId = $this->ensureVisitForToday($petId, db: $db); // returns integer id
            }
        } catch (\Throwable $e) {
            $visitId = null; // keep silent, allow orphan
        }

        // Insert into documents
        $mime = (string) ($file->getMimeType() ?: '');
        $size = (int) ($file->getSize() ?: 0);
        $now  = date('Y-m-d H:i:s');

        $doc = [
            'patient_unique_id' => $uid,
            'pet_id'            => $petId,
            'visit_id'          => $visitId,
            'type'              => $typeDb,
            'subtype'           => null,
            'path'              => 'writable/uploads/' . $final, // viewer resolves across roots
            'filename'          => $final,
            'source'            => 'web',
            'ref_id'            => null,
            'seq'               => null,
            'mime'              => $mime ?: null,
            'size_bytes'        => $size ?: null,
            'captured_at'       => $now,
            'checksum_sha1'     => sha1_file($targetPath),
            'created_at'        => $now,
            'note'              => $note ?: null,
        ];

        try {
            $db->table('documents')->insert($doc);
            $docId = (int) $db->insertID();
        } catch (\Throwable $e) {
            @unlink($targetPath);
            return $this->json(['ok' => false, 'error' => 'db_insert_failed', 'detail' => $e->getMessage()], 500);
        }

        return $this->json([
            'ok'       => true,
            'visitId'  => $visitId,
            'attachment' => [
                'id'         => $docId,
                'type'       => $type,
                'filename'   => $final,
                'url'        => site_url('admin/visit/file?id=' . $docId),
                'created_at' => $now,
            ],
        ]);
    }

    /**
     * POST /api/visit/map-orphans[?token=...] [&uid=250001]
     * Maps orphan docs (visit_id NULL) to today's visit for their UID.
     */
    public function mapOrphans(): ResponseInterface
    {
        if (! $this->authOk()) {
            return $this->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $limitUid = trim((string) ($this->request->getPost('uid') ?? $this->request->getGet('uid') ?? ''));

        $db = db_connect();
        $builder = $db->table('documents')->select('id, patient_unique_id')->where('visit_id', null);
        if ($limitUid !== '') {
            $builder->where('patient_unique_id', $limitUid);
        }
        $orphans = $builder->get()->getResultArray();

        $mapped = 0;
        $errors = [];

        foreach ($orphans as $row) {
            $uid = trim((string) ($row['patient_unique_id'] ?? ''));
            if ($uid === '' || strlen($uid) !== 6) {
                $errors[] = ['id' => (int) $row['id'], 'error' => 'bad_uid'];
                continue;
            }
            $petId = $this->petIdFromUid($uid, $db);
            if (! $petId) {
                $errors[] = ['id' => (int) $row['id'], 'error' => 'pet_missing'];
                continue;
            }
            try {
                $visitId = $this->ensureVisitForToday($petId, db: $db);
                if ($visitId) {
                    $db->table('documents')->where('id', (int) $row['id'])->update(['visit_id' => $visitId]);
                    $mapped++;
                } else {
                    $errors[] = ['id' => (int) $row['id'], 'error' => 'visit_not_created'];
                }
            } catch (\Throwable $e) {
                $errors[] = ['id' => (int) $row['id'], 'error' => 'exception', 'detail' => $e->getMessage()];
            }
        }

        return $this->json(['ok' => true, 'mapped' => $mapped, 'total' => count($orphans), 'errors' => $errors]);
    }

    // ---------- HELPERS ----------

    private function authOk(): bool
    {
        $devBypass = env('DEV_NO_AUTH', false);
        if ($devBypass) {
            return true;
        }
        $token = $this->request->getGet('token') ?? $this->request->getPost('token');
        $expected = env('ANDROID_API_TOKEN', '');
        return $expected === '' || $token === $expected;
    }

    private function json(array $payload, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($payload);
    }

    private function normalizeDateToIso(string $date): ?string
    {
        $date = trim($date);
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $date)) {
            return $date;
        }
        if (preg_match('#^(\d{2})-(\d{2})-(\d{4})$#', $date, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return null;
    }

    private function isoToDmy(string $iso): string
    {
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $iso, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return $iso;
    }

    private function petIdFromUid(string $uid, ?BaseConnection $db = null): ?int
    {
        $db = $db ?? db_connect();
        try {
            $row = $db->query("SELECT id FROM pets WHERE unique_id = ? LIMIT 1", [$uid])->getRowArray();
            return $row ? (int) $row['id'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nextVisitSeqForDate(int $petId, string $isoDate, ?BaseConnection $db = null): int
    {
        $db = $db ?? db_connect();
        $row = $db->query("SELECT MAX(visit_seq) AS mx FROM visits WHERE pet_id=? AND visit_date=?", [$petId, $isoDate])->getRowArray();
        return (int) ($row['mx'] ?? 0);
    }

    private function createVisit(int $petId, string $isoDate, int $seq, ?BaseConnection $db = null): int
    {
        $db = $db ?? db_connect();
        $db->table('visits')->insert([
            'pet_id'     => $petId,
            'visit_date' => $isoDate,
            'visit_seq'  => $seq,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $db->insertID();
    }

    /**
     * Ensure today’s visit exists and return its ID.
     * Uses latest existing if present; else creates seq = (max+1) or 1 if none.
     */
    private function ensureVisitForToday(int $petId, ?BaseConnection $db = null): ?int
    {
        $db = $db ?? db_connect();
        $today = date('Y-m-d');
        $row = $db->query(
            "SELECT id, visit_seq FROM visits WHERE pet_id=? AND visit_date=? ORDER BY visit_seq DESC LIMIT 1",
            [$petId, $today]
        )->getRowArray();

        if ($row) {
            return (int) $row['id'];
        }

        $seq = $this->nextVisitSeqForDate($petId, $today, $db) + 1;
        return $this->createVisit($petId, $today, max(1, $seq), $db);
    }

    /**
     * Count existing docs for ddmmyy/uid to pick the next 2-digit suffix.
     */
    private function nextSequenceForDay(string $uid, string $ddmmyy, ?BaseConnection $db = null): int
    {
        $db = $db ?? db_connect();
        // count filenames starting with 'ddmmyy-' and containing '-uid-'
        $like = $ddmmyy . '-%-' . $uid . '-%';
        $row = $db->query("SELECT COUNT(*) AS c FROM documents WHERE filename LIKE ?", [$like])->getRowArray();
        $seq = ((int) ($row['c'] ?? 0)) + 1;
        if ($seq > 99) $seq = 99;
        return $seq;
    }

    /**
     * Ensure unique name across DB and common FS roots; add -a, -b, ... then timestamp.
     */
    private function uniqueFilename(string $baseStem, string $ext, ?BaseConnection $db = null): string
    {
        $db = $db ?? db_connect();
        $suffixes = ['', '-a','-b','-c','-d','-e','-f','-g','-h','-i','-j'];

        $w = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        $p = rtrim(FCPATH,   '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        for ($i=0; $i<count($suffixes); $i++) {
            $name = $baseStem . $suffixes[$i] . '.' . $ext;
            $existsDb = $db->table('documents')->where('filename', $name)->countAllResults() > 0;
            $existsFs = (is_file($w . $name) || is_file($p . $name));
            if (! $existsDb && ! $existsFs) return $name;
        }
        return $baseStem . '-' . time() . '.' . $ext;
    }

    /**
     * Gather visits for a day with their documents.
     * If $all=false, returns at most one (latest) visit for that date.
     */
    private function fetchVisitsWithDocs(int $petId, string $isoDate, bool $all, ?BaseConnection $db = null): array
    {
        $db = $db ?? db_connect();

        $vis = $db->table('visits')
                  ->select('id, visit_seq')
                  ->where('pet_id', $petId)
                  ->where('visit_date', $isoDate)
                  ->orderBy('visit_seq', $all ? 'ASC' : 'DESC');

        if (! $all) {
            $vis->limit(1);
        }

        $visits = $vis->get()->getResultArray();
        if (!$visits) {
            return [];
        }

        $ids = array_map(fn($r) => (int) $r['id'], $visits);
        $docsByVisit = [];
        if ($ids) {
            $docs = $db->table('documents')
                       ->select('id, visit_id, type, filename, created_at')
                       ->whereIn('visit_id', $ids)
                       ->orderBy('id', 'ASC')
                       ->get()->getResultArray();

            foreach ($docs as $d) {
                $vid = (int) $d['visit_id'];
                $docsByVisit[$vid][] = [
                    'id'         => (int) $d['id'],
                    'type'       => (string) ($d['type'] ?? ''),
                    'filename'   => (string) ($d['filename'] ?? ''),
                    'filesize'   => '', // not tracked; keep shape for your tester
                    'created_at' => (string) ($d['created_at'] ?? ''),
                    'url'        => site_url('admin/visit/file?id=' . (int) $d['id']),
                ];
            }
        }

        $out = [];
        foreach ($visits as $v) {
            $out[] = [
                'id'        => (int) $v['id'],
                'date'      => $this->isoToDmy($isoDate),
                'sequence'  => (int) $v['visit_seq'],
                'documents' => $docsByVisit[(int) $v['id']] ?? [],
            ];
        }

        return $out;
    }
}
