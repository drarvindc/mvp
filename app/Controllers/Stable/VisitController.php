<?php
namespace App\Controllers\Stable;

use CodeIgniter\RESTful\ResourceController;

class VisitController extends ResourceController
{
    protected $format = 'json';
    protected $allowedTypes = ['rx','photo','doc','xray','lab','usg','invoice'];
    protected $allowedMimes = ['image/jpeg','image/png','image/webp','application/pdf'];
    protected $maxBytes = 10485760; // 10 MB
    const STORAGE_BASE = WRITEPATH . 'patients';

    protected function findPetByUid(string $uid)
    {
        $db = \Config\Database::connect();
        return $db->table('pets')->where('unique_id', $uid)->get()->getRowArray();
    }

    protected function ensureVisit(int $petId, string $date, bool $forceNew): array
    {
        $db = \Config\Database::connect();
        if (!$forceNew) {
            $existing = $db->table('visits')
                           ->where(['pet_id'=>$petId,'visit_date'=>$date])
                           ->orderBy('visit_seq','DESC')->get(1)->getRowArray();
            if ($existing) return $existing;
        }

        $db->transStart();
        $row = $db->query('SELECT COALESCE(MAX(visit_seq),0) AS last_seq FROM visits WHERE pet_id=? AND visit_date=? FOR UPDATE', [$petId, $date])->getRowArray();
        $next = (int)($row['last_seq'] ?? 0) + 1;
        $db->table('visits')->insert([
            'pet_id'     => $petId,
            'visit_date' => $date,
            'visit_seq'  => $next,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int)$db->insertID();
        $db->transComplete();
        if (!$db->transStatus()) {
            throw new \RuntimeException('db_txn_failed');
        }
        return ['id'=>$id,'pet_id'=>$petId,'visit_date'=>$date,'visit_seq'=>$next];
    }

    protected function dmyToIso(?string $s): string
    {
        helper('datefmt');
        $iso = dmy_to_iso($s);
        return $iso ?: date('Y-m-d');
    }

    protected function isoToDmy(?string $s): string
    {
        helper('datefmt');
        return iso_to_dmy($s) ?: date('d-m-Y');
    }

    private function toBool($v): bool
    {
        if (is_bool($v)) return $v;
        $s = strtolower((string)$v);
        return in_array($s, ['1','true','yes','on'], true);
    }

    public function open()
    {
        $uid = trim((string)$this->request->getVar('uid'));
        if (!preg_match('/^\d{6}$/', $uid)) {
            return $this->respond(['ok'=>false,'error'=>'uid_invalid'], 400);
        }
        $pet = $this->findPetByUid($uid);
        if (!$pet) return $this->respond(['ok'=>false,'error'=>'uid_not_found'], 404);

        $force = $this->toBool($this->request->getVar('forceNewVisit'));
        $iso = date('Y-m-d'); // today
        try {
            $visit = $this->ensureVisit((int)$pet['id'], $iso, $force);
        } catch (\Throwable $e) {
            return $this->respond(['ok'=>false,'error'=>'db_error'], 500);
        }

        $seq = isset($visit['visit_seq']) ? (int)$visit['visit_seq'] : 1;
        if ($seq < 1) $seq = 1;

        return $this->respond([
            'ok'=>true,
            'visit'=>[
                'id'=>(int)$visit['id'],
                'uid'=>$uid,
                'date'=>$this->isoToDmy($iso),
                'sequence'=>$seq,
                'wasCreated'=>$force
            ]
        ], 200);
    }

    public function upload()
    {
        $uid  = trim((string)$this->request->getVar('uid'));
        $type = strtolower(trim((string)$this->request->getVar('type')));
        $note = (string)$this->request->getVar('note');
        $force = $this->toBool($this->request->getVar('forceNewVisit'));
        $file = $this->request->getFile('file');

        if (!preg_match('/^\d{6}$/', $uid)) return $this->respond(['ok'=>false,'error'=>'uid_invalid'], 400);
        if (!$type || !in_array($type, $this->allowedTypes, true)) return $this->respond(['ok'=>false,'error'=>'type_invalid'], 415);
        if (!$file || !$file->isValid()) return $this->respond(['ok'=>false,'error'=>'file_required'], 400);

        $mime = $file->getMimeType();
        $size = $file->getSize();
        $ext  = strtolower($file->getExtension() ?: pathinfo($file->getName(), PATHINFO_EXTENSION));

        if ($size > $this->maxBytes) return $this->respond(['ok'=>false,'error'=>'file_too_large'], 413);
        if (!in_array($mime, $this->allowedMimes, true)) return $this->respond(['ok'=>false,'error'=>'unsupported_media_type'], 415);
        if (!in_array($ext, ['jpg','jpeg','png','pdf','webp'], true)) return $this->respond(['ok'=>false,'error'=>'unsupported_extension'], 415);

        $pet = $this->findPetByUid($uid);
        if (!$pet) return $this->respond(['ok'=>false,'error'=>'uid_not_found'], 404);

        $iso = date('Y-m-d');
        try {
            $visit = $this->ensureVisit((int)$pet['id'], $iso, $force);
            $visitId = (int)$visit['id'];
        } catch (\Throwable $e) {
            return $this->respond(['ok'=>false,'error'=>'db_error'], 500);
        }

        $yyyy = substr($iso, 0, 4);
        $dir = rtrim(self::STORAGE_BASE, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $yyyy . DIRECTORY_SEPARATOR . $uid;
        if (!is_dir($dir) and !@mkdir($dir, 0775, true)) {
            return $this->respond(['ok'=>false,'error'=>'storage_unwritable'], 500);
        }

        // Unique serial across ANY extension
        $ddmmyy = date('dmy', strtotime($iso));
        $base = "{$ddmmyy}-{$type}-{$uid}";
        $existing = glob($dir . DIRECTORY_SEPARATOR . $base . '.*') ?: [];
        $existing2 = glob($dir . DIRECTORY_SEPARATOR . $base . '-[0-9][0-9].*') ?: [];
        $existing = array_merge($existing, $existing2);
        $next = max(1, count($existing) + 1);
        $name = sprintf('%s-%02d.%s', $base, $next, $ext);

        try {
            $file->move($dir, $name, true);
        } catch (\Throwable $e) {
            return $this->respond(['ok'=>false,'error'=>'upload_failed'], 500);
        }
        $full = $dir . DIRECTORY_SEPARATOR . $name;

        $db = \Config\Database::connect();
        $row = [
            'visit_id'   => $visitId,
            'pet_id'     => (int)$pet['id'],
            'type'       => $type,
            'filename'   => $name,
            'filesize'   => @filesize($full) ?: $size,
            'mime'       => $mime,
            'note'       => $note,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        try { $fields = $db->getFieldNames('documents'); } catch (\Throwable $e) { $fields = array_keys($row); }
        $filtered = array_intersect_key($row, array_flip($fields));

        try {
            $db->table('documents')->insert($filtered);
        } catch (\Throwable $e2) {
            $essentials = ['visit_id','type','filename','filesize','created_at'];
            $filtered2 = array_intersect_key($row, array_flip($essentials));
            try { $db->table('documents')->insert($filtered2); }
            catch (\Throwable $e3) { return $this->respond(['ok'=>false,'error'=>'db_insert_failed'], 500); }
        }
        $docId = (int) $db->insertID();

        return $this->respond([
            'ok'=>true,
            'visitId'=>$visitId,
            'attachment'=>[
                'id'=>$docId,
                'type'=>$type,
                'filename'=>$name,
                'url'=>site_url('admin/visit/file?id='.$docId),
                'created_at'=>date('c'),
            ]
        ], 201);
    }

    // Maps any documents with NULL visit_id to today's latest visit for their pet_id
    public function mapOrphans()
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        $orphans = $db->table('documents')->where('visit_id', null)->get()->getResultArray();
        $mapped = 0; $skipped = 0;

        foreach ($orphans as $d) {
            $petId = (int)($d['pet_id'] ?? 0);
            if (!$petId) { $skipped++; continue; }

            // ensure visit for today (do not force new so it reuses latest)
            $visit = $this->ensureVisit($petId, $today, false);

            try {
                $db->table('documents')->where('id', (int)$d['id'])->update(['visit_id' => (int)$visit['id']]);
                $mapped++;
            } catch (\Throwable $e) {
                $skipped++;
            }
        }

        return $this->respond(['ok'=>true,'date'=>$this->isoToDmy($today),'mapped'=>$mapped,'skipped'=>$skipped], 200);
    }

    public function today()
    {
        $uid = trim((string)$this->request->getGet('uid'));
        $dateIn = trim((string)($this->request->getGet('date') ?: date('Y-m-d')));
        $all = $this->toBool($this->request->getGet('all'));

        if (!preg_match('/^\d{6}$/', $uid)) return $this->respond(['ok'=>false,'error'=>'uid_invalid'], 400);
        $pet = $this->findPetByUid($uid);
        if (!$pet) return $this->respond(['ok'=>false,'error'=>'uid_not_found'], 404);

        $isoDate = $this->dmyToIso($dateIn);

        $db = \Config\Database::connect();
        $qb = $db->table('visits')->where(['pet_id'=>(int)$pet['id'],'visit_date'=>$isoDate]);
        $visits = $all ? $qb->orderBy('visit_seq','ASC')->get()->getResultArray()
                       : $qb->orderBy('visit_seq','DESC')->get(1)->getResultArray();

        $out = [];
        foreach ($visits as $v) {
            $docs = $db->table('documents')->where('visit_id', $v['id'])->orderBy('id','ASC')->get()->getResultArray();
            $doclist = [];
            foreach ($docs as $d) {
                $doclist[] = [
                    'id'=>(int)$d['id'],
                    'type'=>$d['type'] ?? 'file',
                    'filename'=>$d['filename'] ?? '',
                    'filesize'=>isset($d['filesize']) ? strval($d['filesize']) : '',
                    'created_at'=>$d['created_at'] ?? '',
                    'url'=>site_url('admin/visit/file?id=' . $d['id'])
                ];
            }
            $out[] = [
                'id'=>(int)$v['id'],
                'date'=>$this->isoToDmy($v['visit_date']),
                'sequence'=>(int)$v['visit_seq'],
                'documents'=>$doclist
            ];
        }

        return $this->respond(['ok'=>true,'date'=>$this->isoToDmy($isoDate),'results'=>$out], 200);
    }

    public function byDate() { return $this->today(); }
}
