<?php
declare(strict_types=1);

namespace App\Controllers\Stable;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;

class VisitController extends BaseController
{
    /**
     * POST /stable/visit/upload
     * Required:
     *   - uid  (6 digits)
     *   - type = rx|photo|doc|xray|lab|usg|invoice
     * File(s):
     *   - file  (single)  OR  file[] / files[]  (multiple)
     * Notes:
     *   - note   (single note for all files) OR note[] (per file, matched by index)
     *
     * Single-file response (unchanged):
     *   { ok:true, visitId: <int|null>, attachment: { id, type, filename, url, created_at } }
     *
     * Multi-file response:
     *   { ok:true, visitId: <int|null>, attachments: [ { ... }, ... ] }
     */
    public function upload(): ResponseInterface
    {
        $uid  = trim((string) ($this->request->getPost('uid') ?? ''));
        $type = strtolower(trim((string) ($this->request->getPost('type') ?? '')));

        if ($uid === '' || strlen($uid) !== 6) {
            return $this->json(['ok' => false, 'error' => 'uid_invalid'], 422);
        }

        $allowed = ['rx','photo','doc','xray','lab','usg','invoice'];
        if ($type === '' || ! in_array($type, $allowed, true)) {
            return $this->json(['ok' => false, 'error' => 'type_invalid', 'allowed' => $allowed], 422);
        }
        $typeDb = ($type === 'rx') ? 'prescription' : $type;

        // Collect files (single or multiple)
        $files = [];
        $one = $this->request->getFile('file');
        if ($one && $one->isValid()) {
            $files = [$one];
        } else {
            $files = $this->request->getFileMultiple('file');
            if (empty($files)) {
                $files = $this->request->getFileMultiple('files');
            }
        }
        if (empty($files)) {
            return $this->json(['ok' => false, 'error' => 'file_missing_or_invalid'], 422);
        }

        // Normalize notes to array aligned to files (fixes "Array to string conversion")
        $noteInput = $this->request->getPost('note');
        if (is_array($noteInput)) {
            $notes = array_values($noteInput);
        } elseif (is_string($noteInput) && $noteInput !== '') {
            $notes = array_fill(0, count($files), $noteInput);
        } else {
            $notes = array_fill(0, count($files), null);
        }

        $db    = db_connect();
        $petId = $this->petIdFromUid($uid, $db); // may be null; still accept upload

        // Try to attach to today's visit if we can resolve petId
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
                'note'              => $notes[$i] ?? null, // <- always string|null
            ];

            try {
                $db->table('documents')->insert($doc);
                $docId = (int) $db->insertID();
                $attachments.append([
                    'id'         => $docId,
                    'type'       => $type,
                    'filename'   => $final,
                    'url'        => site_url('admin/visit/file?id=' . $docId),
                    'created_at' => $now,
                ]);
            } catch (\Throwable $e) {
                @unlink($targetPath);
                $attachments[] = ['error' => 'db_insert_failed', 'detail' => $e->getMessage()];
            }
        }

        // Preserve legacy single-file shape
        if (count($attachments) === 1 && isset($attachments[0]['id'])) {
            return $this->json([
                'ok'         => true,
                'visitId'    => $visitId,
                'attachment' => $attachments[0],
            ]);
        }

        return $this->json([
            'ok'          => true,
            'visitId'     => $visitId,
            'attachments' => $attachments,
        ]);
    }

    // ----------------- Helpers -----------------

    private function json(array $payload, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($payload);
    }

    private function petIdFromUid(string $uid, ?BaseConnection $db = null): ?int
    {
        $db ??= db_connect();

        // Explicit live schema (your server): pets.unique_id
        $cfgTable  = env('PETS_TABLE', 'pets');
        $cfgColumn = env('PETS_UID_COLUMN', 'unique_id');

        try {
            $row = $db->query("SELECT id FROM {$cfgTable} WHERE {$cfgColumn} = ? LIMIT 1", [$uid])->getRowArray();
            if ($row && isset($row['id'])) {
                return (int) $row['id'];
            }
        } catch (\Throwable $e) { /* fallback below */ }

        // Fallback probing if envs differ later
        static $cached = null;
        if ($cached === null) {
            $candidates = [
                ['pets','unique_id'],
                ['pets','patient_unique_id'],
                ['patients','unique_id'],
                ['patients','patient_unique_id'],
            ];
            foreach ($candidates as [$t,$c]) {
                try {
                    $probe = $db->query("SHOW COLUMNS FROM {$t} LIKE ?", [$c])->getResultArray();
                    if ($probe) { $cached = [$t,$c]; break; }
                } catch (\Throwable $e) {}
            }
            if ($cached === null) $cached = [$cfgTable, $cfgColumn];
        }

        [$t,$c] = $cached;
        try {
            $row = $db->query("SELECT id FROM {$t} WHERE {$c} = ? LIMIT 1", [$uid])->getRowArray();
            return $row ? (int) $row['id'] : null;
        } catch (\Throwable $e) {
            return null;
        }
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
}
