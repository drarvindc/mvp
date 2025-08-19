<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Visits API
 *
 * Endpoints:
 *  - POST /api/visit/open            uid=250001 [&forceNewVisit=1]
 *  - GET  /api/visit/by-date         token=&uid=&date=dd-mm-yyyy|yyyy-mm-dd [&all=1]
 *  - POST /api/visit/upload          token=&uid=&type=rx|photo|doc|xray|lab|usg|invoice&file=@... [&note=...]
 *                                    (multi: file[] or files[], optional note[] to match order)
 *  - POST /api/visit/map-orphans     token=[&uid=250001]
 *
 * Auth:
 *  - In DEV if DEV_NO_AUTH=true, auth is bypassed
 *  - Else token must equal ANDROID_API_TOKEN
 */
class VisitController extends BaseController
{
    // ---------- PUBLIC ENDPOINTS ----------

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
            return $this->json(['ok' => true, 'date' => $this->isoToDmy($iso), 'results' => []]);
        }

        $visits = $this->fetchVisitsWithDocs($petId, $iso, $all === 1, $db);

        return $this->json([
            'ok'      => true,
            'date'    => $this->isoToDmy($iso),
            'results' => $visits,
        ]);
    }

    /**
     * Enhanced: supports single file (legacy) and multiple files in one request.
     * - Single: returns { ok, visitId, attachment: {...} }
     * - Multi:  returns { ok, visitId, attachments: [ {...}, {...} ] }
     * Notes:
     * - `note` can be a single string or note[] matching file order.
     * - Accepts file in any of: 'file' (single), 'file[]', or 'files[]'.
     */
    public function upload(): ResponseInterface
    {
        if (! $this->authOk()) {
            return $this->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $uid   = trim((string) ($this->request->getPost('uid') ?? ''));
        $type  = strtolower(trim((string) ($this->request->getPost('type') ?? '')));
        $note  = $this->request->getPost('note'); // can be string or array
        $file  = $this->request->getFile('file');

        if ($uid === '' || strlen($uid) !== 6) {
            return $this->json(['ok' => false, 'error' => 'uid_invalid'], 422);
        }

        $allowed = ['rx','photo','doc','xray','lab','usg','invoice'];
        if ($type === '' || ! in_array($type, $allowed, true)) {
            return $this->json(['ok' => false, 'error' => 'type_invalid', 'allowed' => $allowed], 422);
        }
        $typeDb = ($type === 'rx') ? 'prescription' : $type;

        $db    = db_connect();
        $petId = $this->petIdFromUid($uid, $db); // may be null; we still accept upload

        // Collect files: single or multiple
        $files = [];
        if ($file && $file->isValid()) {
            $files = [$file]; // legacy single
        } else {
            // try file[] then files[]
            $files = $this->request->getFileMultiple('file');
            if (empty($files)) {
                $files = $this->request->getFileMultiple('files');
            }
        }

        if (empty($files)) {
            return $this->json(['ok' => false, 'error' => 'file_missing_or_invalid'], 422);
        }

        // Normalize notes to array aligned with $files
        $notes = [];
        if (is_array($note)) {
            $notes = array_values($note);
        } elseif (is_string($note) && $note !== '') {
            $notes = array_fill(0, count($files), $note);
        } else {
            $notes = array_fill(0, count($files), null);
        }

        // Prepare visit mapping (best effort)
        $visitId = null;
        try {
            if ($petId) {
                $visitId = $this->ensureVisitForToday($petId, db: $db);
            }
        } catch (\Throwable $e) {
            $visitId = null;
        }

        $ddmmyy    = date('dmy');
        $targetDir = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads';
        @is_dir($targetDir) || @mkdir($targetDir, 0775, true);

        $attachments = [];
        foreach ($files as $i => $f) {
            if (! $f || ! $f->isValid()) {
                $attachments[] = ['error' => 'file_invalid'];
                continue;
            }

            $seq   = $this->nextSequenceForDay($uid, $ddmmyy, $db);
            $ext   = strtolower($f->getClientExtension() ?: pathinfo($f->getName(), PATHINFO_EXTENSION) ?: 'bin');
            $stem  = sprintf('%s-%s-%s-%02d', $ddmmyy, $type, $uid, $seq);
            $final = $this->uniqueFilename($stem, $ext, $db);

            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $final;
            try {
                $f->move($targetDir, $final, true);
            } catch (\Throwable $e) {
                $attachments[] = ['error' => 'file_move_failed', 'detail' => $e->getMessage()];
                continue;
            }

            $mime = (string) ($f->getMimeType() ?: '');
            $size = (int) ($f->getSize() ?: 0);
            $now  = date('Y-m-d H:i:s');

            $doc = [
                'patient_unique_id' => $uid,
                'pet_id'            => $petId,
                'visit_id'          => $visitId,
                'type'              => $typeDb,
                'subtype'           => null,
                'path'              => 'writable/uploads/' . $final,
                'filename'          => $final,
                'source'            => 'web',
                'ref_id'            => null,
                'seq'               => null,
                'mime'              => $mime ?: null,
                'size_bytes'        => $size ?: null,
                'captured_at'       => $now,
                'checksum_sha1'     => sha1_file($targetPath),
                'created_at'        => $now,
                'note'              => $notes[$i] ?? null,
            ];

            try {
                $db->table('documents')->insert($doc);
                $docId = (int) $db->insertID();
                $attachments[] = [
                    'id'         => $docId,
                    'type'       => $type,
                    'filename'   => $final,
                    'url'        => site_url('admin/visit/file?id=' . $docId),
                    'created_at' => $now,
                ];
            } catch (\Throwable $e) {
                @unlink($targetPath);
                $attachments[] = ['error' => 'db_insert_failed', 'detail' => $e->getMessage()];
            }
        }

        // Keep legacy response for single-file calls
        if (count($attachments) === 1 && isset($attachments[0]['id'])) {
            return $this->json([
                'ok'        => true,
                'visitId'   => $visitId,
                'attachment'=> $attachments[0],
            ]);
        }

        // Multi-file (or single with error)
        return $this->json([
            'ok'          => true,
            'visitId'     => $visitId,
            'attachments' => $attachments,
        ]);
    }

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
        if ($devBypass) return true;
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
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $date)) return $date;
        if (preg_match('#^(\d{2})-(\d{2})-(\d{4})$#', $date, $m)) return "{$m[3]}-{$m[2]}-{$m[1]}";
        return null;
    }

    private function isoToDmy(string $iso): string
    {
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $iso, $m)) return "{$m[3]}-{$m[2]}-{$m[1]}";
        return $iso;
    }

    /**
     * Robust pet lookup; auto-detects pets.unique_id (your schema) and supports .env overrides.
     */
    private function petIdFromUid(string $uid, ?BaseConnection $db = null): ?int
    {
        $db ??= db_connect();

        // Optional explicit config
        $cfgTable  = env('PETS_TABLE', 'pets');
        $cfgColumn = env('PETS_UID_COLUMN', 'unique_id');

        // Try configured/default
        try {
            $row = $db->query("SELECT id FROM {$cfgTable} WHERE {$cfgColumn} = ? LIMIT 1", [$uid])->getRowArray();
            if ($row && isset($row['id'])) return (int) $row['id'];
        } catch (\Throwable $e) {
            // fall through
        }

        // Auto-detect common variants once per request
        static $cached = null;
        if ($cached === null) {
            $candidates = [
                ['pets', 'unique_id'],          // your live schema
                ['pets', 'patient_unique_id'],
                ['patients', 'unique_id'],
                ['patients', 'patient_unique_id'],
                ['animals', 'unique_id'],
                ['animals', 'patient_unique_id'],
            ];
            foreach ($candidates as [$t, $c]) {
                try {
                    $probe = $db->query("SHOW COLUMNS FROM {$t} LIKE ?", [$c])->getResultArray();
                    if ($probe) { $cached = [$t, $c]; break; }
                } catch (\Throwable $e) {}
            }
            if ($cached === null) $cached = [$cfgTable, $cfgColumn];
        }

        [$t, $c] = $cached;
        try {
            $row = $db->query("SELECT id FROM {$t} WHERE {$c} = ? LIMIT 1", [$uid])->getRowArray();
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

    private function ensureVisitForToday(int $petId, ?BaseConnection $db = null): ?int
    {
        $db = $db ?? db_connect();
        $today = date('Y-m-d');
        $row = $db->query(
            "SELECT id, visit_seq FROM visits WHERE pet_id=? AND visit_date=? ORDER BY visit_seq DESC LIMIT 1",
            [$petId, $today]
        )->getRowArray();

        if ($row) return (int) $row['id'];

        $seq = $this->nextVisitSeqForDate($petId, $today, $db) + 1;
        return $this->createVisit($petId, $today, max(1, $seq), $db);
    }

    private function nextSequenceForDay(string $uid, string $ddmmyy, ?BaseConnection $db = null): int
    {
        $db = $db ?? db_connect();
        $like = $ddmmyy . '-%-' . $uid . '-%';
        $row = $db->query("SELECT COUNT(*) AS c FROM documents WHERE filename LIKE ?", [$like])->getRowArray();
        $seq = ((int) ($row['c'] ?? 0)) + 1;
        if ($seq > 99) $seq = 99;
        return $seq;
    }

    private function uniqueFilename(string $baseStem, string $ext, ?BaseConnection $db = null): string
    {
        $db = $db ?? db_connect();
        $suffixes = ['', '-a','-b','-c','-d','-e','-f','-g','-h','-i','-j'];

        $w = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        $p = rtrim(FCPATH,   '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        foreach ($suffixes as $suf) {
            $name = $baseStem . $suf . '.' . $ext;
            $existsDb = $db->table('documents')->where('filename', $name)->countAllResults() > 0;
            $existsFs = (is_file($w . $name) || is_file($p . $name));
            if (! $existsDb && ! $existsFs) return $name;
        }
        return $baseStem . '-' . time() . '.' . $ext;
    }

    private function fetchVisitsWithDocs(int $petId, string $isoDate, bool $all, ?BaseConnection $db = null): array
    {
        $db = $db ?? db_connect();

        $vis = $db->table('visits')
                  ->select('id, visit_seq')
                  ->where('pet_id', $petId)
                  ->where('visit_date', $isoDate)
                  ->orderBy('visit_seq', $all ? 'ASC' : 'DESC');

        if (! $all) $vis->limit(1);

        $visits = $vis->get()->getResultArray();
        if (!$visits) return [];

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
                    'filesize'   => '',
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
