<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class VisitController extends ResourceController
{
    protected $format = 'json';

    protected function findPetByUid(string $uid)
    {
        $db = \Config\Database::connect();
        return $db->table('pets')->where('unique_id', $uid)->get()->getRowArray();
    }

    public function open()
    {
        $uid   = trim((string) $this->request->getVar('uid'));
        $force = $this->toBool($this->request->getVar('forceNewVisit'));

        if ($uid === '') {
            return $this->failValidationErrors('Missing uid');
        }

        $pet = $this->findPetByUid($uid);
        if (!$pet) {
            return $this->respond(['ok' => false, 'error' => 'Pet not found for UID'], 404);
        }

        $date = date('Y-m-d');
        $db   = \Config\Database::connect();

        if (!$force) {
            $existing = $db->table('visits')
                           ->where(['pet_id' => $pet['id'], 'visit_date' => $date])
                           ->orderBy('visit_seq', 'DESC')
                           ->get(1)->getRowArray();
            if ($existing) {
                return $this->respond([
                    'ok'    => true,
                    'visit' => [
                        'id'       => (string) $existing['id'],
                        'date'     => $existing['visit_date'],
                        'sequence' => (int) $existing['visit_seq'],
                    ],
                ]);
            }
        }

        $db->transStart();

        $row = $db->query(
            'SELECT COALESCE(MAX(visit_seq), 0) AS last_seq
               FROM visits
              WHERE pet_id = ? AND visit_date = ?
              FOR UPDATE',
            [$pet['id'], $date]
        )->getRowArray();
        $nextSeq = (int) ($row['last_seq'] ?? 0) + 1;

        $db->table('visits')->insert([
            'pet_id'     => $pet['id'],
            'visit_date' => $date,
            'visit_seq'  => $nextSeq,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $visitId = $db->insertID();

        $db->transComplete();
        if (!$db->transStatus()) {
            return $this->respond(['ok' => false, 'error' => 'DB transaction failed'], 500);
        }

        return $this->respond([
            'ok'    => true,
            'visit' => [
                'id'       => (string) $visitId,
                'date'     => $date,
                'sequence' => $nextSeq,
            ],
        ]);
    }

    public function today()
    {
        $uid  = trim((string) $this->request->getVar('uid'));
        $date = trim((string) ($this->request->getVar('date') ?? date('Y-m-d')));
        $all  = $this->toBool($this->request->getVar('all'));

        if ($uid === '') {
            return $this->failValidationErrors('Missing uid');
        }

        $pet = $this->findPetByUid($uid);
        if (!$pet) {
            return $this->respond(['ok' => false, 'error' => 'Pet not found for UID'], 404);
        }

        $db = \Config\Database::connect();

        $builder = $db->table('visits')->where(['pet_id' => $pet['id'], 'visit_date' => $date]);
        $visits = $all
            ? $builder->orderBy('visit_seq', 'ASC')->get()->getResultArray()
            : ($builder->orderBy('visit_seq', 'DESC')->get(1)->getResultArray());

        $out = [];
        foreach ($visits as $v) {
            $docs = $db->table('documents')
                       ->where('visit_id', $v['id'])
                       ->orderBy('id', 'ASC')
                       ->get()->getResultArray();

            $out[] = [
                'id'       => (string) $v['id'],
                'date'     => $v['visit_date'],
                'sequence' => (int) $v['visit_seq'],
                'documents'=> array_map(static function ($d) {
                    return [
                        'id'         => (int) $d['id'],
                        'visit_id'   => (int) $d['visit_id'],
                        'type'       => $d['type'] ?? 'file',
                        'filename'   => $d['filename'] ?? '',
                        'filesize'   => (string) ($d['filesize'] ?? 0),
                        'created_at' => $d['created_at'] ?? '',
                        'url'        => site_url('admin/visit/file?id=' . $d['id']),
                    ];
                }, $docs),
            ];
        }

        return $this->respond([
            'ok'      => true,
            'date'    => $date,
            'results' => $out,
        ]);
    }

    public function upload()
    {
        $uid     = trim((string) $this->request->getVar('uid'));
        $visitId = (int) $this->request->getVar('visitId'); // optional; if missing we attach to latest for today
        $type    = trim((string) $this->request->getVar('type'));
        $file    = $this->request->getFile('file');
        $backfill= $this->toBool($this->request->getVar('backfill') ?? '1');

        if ($uid === '' || !$file || !$file->isValid()) {
            return $this->failValidationErrors('uid and file are required');
        }

        $pet = $this->findPetByUid($uid);
        if (!$pet) {
            return $this->respond(['ok' => false, 'error' => 'Pet not found for UID'], 404);
        }

        $db = \Config\Database::connect();

        // Determine target visit
        if (!$visitId) {
            $today = date('Y-m-d');
            $latest = $db->table('visits')
                         ->where(['pet_id' => $pet['id'], 'visit_date' => $today])
                         ->orderBy('visit_seq', 'DESC')->get(1)->getRowArray();
            if (!$latest) {
                // create first visit of the day if none
                $db->table('visits')->insert([
                    'pet_id'     => $pet['id'],
                    'visit_date' => $today,
                    'visit_seq'  => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $visitId = (int) $db->insertID();
            } else {
                $visitId = (int) $latest['id'];
            }
        }

        // Storage: writable/patients/YYYY/UID/
        $dateForPath = date('Y-m-d');
        $yyyy  = substr($dateForPath, 0, 4);
        $base  = WRITEPATH . 'patients' . DIRECTORY_SEPARATOR . $yyyy . DIRECTORY_SEPARATOR . $uid;
        if (!is_dir($base)) {
            @mkdir($base, 0775, true);
        }

        $ext      = strtolower($file->getExtension() ?: pathinfo($file->getName(), PATHINFO_EXTENSION));
        $safeType = $type !== '' ? preg_replace('/[^a-z0-9\-]+/i', '', $type) : 'file';
        $filename = date('dm y').'-'.$safeType.'-'.$uid.'.'.($ext ?: 'dat');
        $filename = str_replace(' ', '', $filename);

        $file->move($base, $filename, true);
        $fullpath = $base . DIRECTORY_SEPARATOR . $filename;

        // Insert into documents
        $db->table('documents')->insert([
            'visit_id'   => $visitId,
            'pet_id'     => $pet['id'],
            'type'       => $type ?: 'file',
            'filename'   => $filename,
            'filesize'   => @filesize($fullpath) ?: 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $docId = (int) $db->insertID();

        // Optional backfill: link any same-day orphan docs (pet_id matches, visit_id NULL) to this visit
        if ($backfill) {
            $today = date('Y-m-d');
            $db->query(
                'UPDATE documents d
                   JOIN visits v ON v.id = ?
                 SET d.visit_id = v.id
                 WHERE d.pet_id = ?
                   AND d.visit_id IS NULL',
                [$visitId, $pet['id']]
            );
        }

        return $this->respond([
            'ok'       => true,
            'visitId'  => (string) $visitId,
            'document' => [
                'id'        => $docId,
                'type'      => $type ?: 'file',
                'filename'  => $filename,
                'url'       => site_url('admin/visit/file?id=' . $docId),
                'created_at'=> date('Y-m-d H:i:s'),
            ],
        ]);
    }

    private function toBool($val): bool
    {
        if (is_bool($val)) return $val;
        $v = strtolower((string) $val);
        return in_array($v, ['1', 'true', 'yes', 'on'], true);
    }
}
