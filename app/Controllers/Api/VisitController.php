<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API endpoints used by Visits-Lite and Admin visits view.
 * - GET  /api/visit/by-date   → list visits + docs for a given UID and date (or today)
 * - POST /api/visit/open      → ensure today's visit exists (optionally force a new seq)
 * - POST /api/visit/upload    → proxy to Stable\VisitController::upload (keeps your tested flow)
 */
class VisitController extends BaseController
{
    // --------------------------
    // NEW: list by date / today
    // --------------------------
    // GET /api/visit/by-date?uid=250001&today=1&all=1
    // or GET /api/visit/by-date?uid=250001&date=11-08-2025
    public function byDate(): ResponseInterface
    {
        $uid   = trim((string) ($this->request->getGet('uid') ?? ''));
        $today = (string) ($this->request->getGet('today') ?? '') === '1';
        $all   = (string) ($this->request->getGet('all') ?? '') === '1';
        $dateQ = trim((string) ($this->request->getGet('date') ?? ''));

        if ($uid === '' || strlen($uid) !== 6) {
            return $this->json(['ok' => false, 'error' => 'uid_invalid'], 422);
        }

        $iso = $today ? date('Y-m-d') : $this->toIso($dateQ);
        if (! $iso) {
            return $this->json(['ok' => false, 'error' => 'date_invalid'], 422);
        }

        $db    = db_connect();
        $petId = $this->petIdFromUid($uid, $db);

        $visits = [];
        if ($petId) {
            $visits = $this->fetchVisitsForDate($petId, $iso, $db, $all);
        }

        // Fallback: show docs tied to UID+date even if no visit rows exist
        if (empty($visits)) {
            $docs = $this->fetchDocsForUidAndDate($uid, $iso, $db);
            if (! empty($docs)) {
                $visits[] = [
                    'id'        => null,
                    'date'      => $this->toDmy($iso),
                    'sequence'  => 0,
                    'documents' => $docs,
                ];
            }
        }

        return $this->json([
            'ok'      => true,
            'date'    => $this->toDmy($iso),
            'results' => $visits,
        ]);
    }

    // --------------------------
    // NEW: open today's visit
    // --------------------------
    // POST /api/visit/open
    // uid=250001 [&forceNewVisit=1]
    public function open(): ResponseInterface
    {
        $uid = trim((string) ($this->request->getPost('uid') ?? ''));
        if ($uid === '' || strlen($uid) !== 6) {
            return $this->json(['ok' => false, 'error' => 'uid_invalid'], 422);
        }

        $forceNew = (string) ($this->request->getPost('forceNewVisit') ?? '') === '1';

        $db    = db_connect();
        $petId = $this->petIdFromUid($uid, $db);

        if (! $petId) {
            // No pet found: return a stub so UI doesn’t block
            return $this->json([
                'ok'    => true,
                'visit' => [
                    'id'         => null,
                    'uid'        => $uid,
                    'date'       => date('d-m-Y'),
                    'sequence'   => 0,
                    'wasCreated' => false,
                ],
            ]);
        }

        $todayIso = date('Y-m-d');

        if ($forceNew) {
            $seq     = $this->nextVisitSeqForDate($petId, $todayIso, $db) + 1;
            $visitId = $this->createVisit($petId, $todayIso, max(1, $seq), $db);

            return $this->json([
                'ok'    => true,
                'visit' => [
                    'id'         => $visitId,
                    'uid'        => $uid,
                    'date'       => date('d-m-Y'),
                    'sequence'   => $seq,
                    'wasCreated' => true,
                ],
            ]);
        }

        $visitId = $this->ensureVisitForDate($petId, $todayIso, $db, $created);
        $seq     = $this->currentVisitSeq($petId, $todayIso, $db);

        return $this->json([
            'ok'    => true,
            'visit' => [
                'id'         => $visitId,
                'uid'        => $uid,
                'date'       => date('d-m-Y'),
                'sequence'   => $seq,
                'wasCreated' => (bool) $created,
            ],
        ]);
    }

    // ---------------------------------------------------
    // SAFE: keep upload endpoint working via proxy
    // ---------------------------------------------------
    // If you already route testers to Api\VisitController::upload,
    // this forwards to the Stable upload you tested earlier.
    public function upload(): ResponseInterface
    {
        // Delegate to Stable controller to avoid code duplication.
        $stable = new \App\Controllers\Stable\VisitController();
        return $stable->upload();
    }

    // ---------------- helpers ----------------

    private function json(array $payload, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($payload);
    }

    private function toIso(string $in): ?string
    {
        $in = trim($in);
        if ($in === '') return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $in)) return $in;
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $in)) {
            [$d,$m,$y] = explode('-', $in);
            return sprintf('%04d-%02d-%02d', (int)$y, (int)$m, (int)$d);
        }
        return null;
    }

    private function toDmy(string $iso): string
    {
        [$y,$m,$d] = explode('-', $iso);
        return sprintf('%02d-%02d-%04d', (int)$d, (int)$m, (int)$y);
    }

    private function petIdFromUid(string $uid, ?BaseConnection $db = null): ?int
    {
        $db ??= db_connect();
        // Live schema: pets.unique_id
        $row = $db->query("SELECT id FROM pets WHERE unique_id = ? LIMIT 1", [$uid])->getRowArray();
        return $row ? (int) $row['id'] : null;
    }

    private function ensureVisitForDate(int $petId, string $isoDate, BaseConnection $db, ?bool &$created = null): ?int
    {
        $row = $db->query(
            "SELECT id FROM visits WHERE pet_id=? AND visit_date=? ORDER BY visit_seq ASC LIMIT 1",
            [$petId, $isoDate]
        )->getRowArray();

        if ($row) { $created = false; return (int) $row['id']; }

        $seq     = $this->nextVisitSeqForDate($petId, $isoDate, $db) + 1;
        $created = true;
        return $this->createVisit($petId, $isoDate, max(1, $seq), $db);
    }

    private function currentVisitSeq(int $petId, string $isoDate, BaseConnection $db): int
    {
        $row = $db->query("SELECT MAX(visit_seq) AS mx FROM visits WHERE pet_id=? AND visit_date=?", [$petId, $isoDate])->getRowArray();
        return (int) ($row['mx'] ?? 1);
    }

    private function nextVisitSeqForDate(int $petId, string $isoDate, BaseConnection $db): int
    {
        $row = $db->query("SELECT MAX(visit_seq) AS mx FROM visits WHERE pet_id=? AND visit_date=?", [$petId, $isoDate])->getRowArray();
        return (int) ($row['mx'] ?? 0);
    }

    private function createVisit(int $petId, string $isoDate, int $seq, BaseConnection $db): int
    {
        $db->table('visits')->insert([
            'pet_id'     => $petId,
            'visit_date' => $isoDate,
            'visit_seq'  => $seq,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $db->insertID();
    }

    private function fetchVisitsForDate(int $petId, string $isoDate, BaseConnection $db, bool $all): array
    {
        $rows = $db->query(
            $all
                ? "SELECT id, visit_seq FROM visits WHERE pet_id=? AND visit_date=? ORDER BY visit_seq ASC"
                : "SELECT id, visit_seq FROM visits WHERE pet_id=? AND visit_date=? ORDER BY visit_seq ASC LIMIT 1",
            [$petId, $isoDate]
        )->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $docs = $this->fetchDocsForVisit((int)$r['id'], $db);
            $out[] = [
                'id'        => (int) $r['id'],
                'date'      => $this->toDmy($isoDate),
                'sequence'  => (int) $r['visit_seq'],
                'documents' => $docs,
            ];
        }
        return $out;
    }

    private function fetchDocsForVisit(int $visitId, BaseConnection $db): array
    {
        $docs = $db->query(
            "SELECT id, type, filename, created_at FROM documents WHERE visit_id=? ORDER BY id ASC",
            [$visitId]
        )->getResultArray();

        foreach ($docs as &$d) {
            $d['filesize'] = ''; // optional placeholder
            $d['url']      = site_url('admin/visit/file?id=' . $d['id']);
        }
        return $docs;
    }

    private function fetchDocsForUidAndDate(string $uid, string $isoDate, BaseConnection $db): array
    {
        // fallback match: filename pattern encodes ddmmyy + uid
        $like = date('dmy', strtotime($isoDate)) . '-%-' . $uid . '-%';
        $docs = $db->query(
            "SELECT id, type, filename, created_at
             FROM documents
             WHERE patient_unique_id = ? AND filename LIKE ?
             ORDER BY id ASC",
            [$uid, $like]
        )->getResultArray();

        foreach ($docs as &$d) {
            $d['filesize'] = '';
            $d['url']      = site_url('admin/visit/file?id=' . $d['id']);
        }
        return $docs;
    }
}
