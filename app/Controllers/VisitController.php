<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class VisitController extends ResourceController
{
    protected $format = 'json';

    /**
     * POST /api/visit/open
     * Params: uid, forceNewVisit (optional: 1/true/yes/on)
     */
    public function open()
    {
        $uid   = trim((string) $this->request->getVar('uid'));
        $force = $this->toBool($this->request->getVar('forceNewVisit'));

        if ($uid === '') {
            return $this->failValidationErrors('Missing uid');
        }

        $patient = model('PatientModel')->where('uid', $uid)->first();
        if (!$patient) {
            return $this->respond(['ok' => false, 'error' => 'Patient not found'], 404);
        }

        $today       = date('Y-m-d');
        $visitModel  = model('VisitModel');

        if (!$force) {
            if ($existing = $visitModel->latestFor($patient['id'], $today)) {
                return $this->respond([
                    'ok'    => true,
                    'visit' => [
                        'id'       => (string) $existing['id'],
                        'date'     => $existing['date'],
                        'sequence' => (int) $existing['sequence'],
                    ],
                ]);
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Lock rows to compute next sequence safely
        $row = $db->query(
            'SELECT COALESCE(MAX(sequence), 0) AS last_seq
               FROM visits
              WHERE patient_id = ? AND date = ?
              FOR UPDATE',
            [$patient['id'], $today]
        )->getRowArray();

        $nextSeq = (int) ($row['last_seq'] ?? 0) + 1;

        $visitId = model('VisitModel')->insert([
            'patient_id' => $patient['id'],
            'date'       => $today,
            'sequence'   => $nextSeq,
            'created_at' => date('Y-m-d H:i:s'),
        ], true);

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->respond(['ok' => false, 'error' => 'DB transaction failed'], 500);
        }

        return $this->respond([
            'ok'    => true,
            'visit' => [
                'id'       => (string) $visitId,
                'date'     => $today,
                'sequence' => $nextSeq,
            ],
        ]);
    }

    /**
     * POST /api/visit/upload
     * Params: visitId, type (rx|lab|img|other...), file (multipart)
     * Saves to /storage/patients/YYYY/UID/ and records in attachments.
     */
    public function upload()
    {
        $visitId = (int) $this->request->getVar('visitId');
        $type    = trim((string) $this->request->getVar('type'));
        $file    = $this->request->getFile('file');

        if (!$visitId || !$file || !$file->isValid()) {
            return $this->failValidationErrors('visitId and file are required');
        }

        $visit = model('VisitModel')->find($visitId);
        if (!$visit) {
            return $this->respond(['ok' => false, 'error' => 'Visit not found'], 404);
        }

        $patient = model('PatientModel')->find($visit['patient_id']);
        if (!$patient) {
            return $this->respond(['ok' => false, 'error' => 'Patient not found'], 404);
        }

        $uid     = $patient['uid'];
        $date    = $visit['date'];
        $yyyy    = substr($date, 0, 4);

        // Make dir: /writable/patients/YYYY/UID/
        $base = WRITEPATH . 'patients' . DIRECTORY_SEPARATOR . $yyyy . DIRECTORY_SEPARATOR . $uid;
        if (!is_dir($base)) {
            @mkdir($base, 0775, true);
        }

        // Filename: DDMMYY-{type}-{uid}[-v{seq}].ext
        $ddmmyy   = date('dmy', strtotime($date));
        $ext      = strtolower($file->getExtension() ?: pathinfo($file->getName(), PATHINFO_EXTENSION));
        $seq      = (int) $visit['sequence'];
        $seqPart  = $seq > 1 ? ('-v' . $seq) : '';
        $safeType = $type !== '' ? preg_replace('/[^a-z0-9\-]+/i', '', $type) : 'file';
        $filename = "{$ddmmyy}-{$safeType}-{$uid}{$seqPart}." . ($ext ?: 'dat');

        $file->move($base, $filename, true);
        $fullpath = $base . DIRECTORY_SEPARATOR . $filename;

        // Save attachment
        $db = \Config\Database::connect();
        $db->table('attachments')->insert([
            'visit_id'   => $visitId,
            'type'       => $type ?: 'file',
            'filename'   => $filename,
            'filesize'   => @filesize($fullpath) ?: 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $attachId = $db->insertID();

        // Public (or admin) URL to serve the file; adapt to your route/controller if different
        $url = site_url('admin/visit/file?id=' . $attachId);

        return $this->respond([
            'ok'        => true,
            'visitId'   => (string) $visitId,
            'attachment'=> [
                'id'        => (int) $attachId,
                'type'      => $type ?: 'file',
                'filename'  => $filename,
                'url'       => $url,
                'created_at'=> date('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * GET /api/visit/today?uid=XXXX[&all=1]
     * When all=1, returns all visits for today; otherwise latest only.
     * Includes attachments for returned visits.
     */
    public function today()
    {
        $uid = trim((string) $this->request->getVar('uid'));
        if ($uid === '') {
            return $this->failValidationErrors('Missing uid');
        }

        $patient = model('PatientModel')->where('uid', $uid)->first();
        if (!$patient) {
            return $this->respond(['ok' => false, 'error' => 'Patient not found'], 404);
        }

        $today      = date('Y-m-d');
        $visitModel = model('VisitModel');

        $all  = $this->toBool($this->request->getVar('all'));
        $db   = \Config\Database::connect();

        $visits = $all
            ? $visitModel->allForDate($patient['id'], $today)
            : array_filter([(array) $visitModel->latestFor($patient['id'], $today)]);

        $out = [];
        foreach ($visits as $v) {
            if (!$v) continue;

            $atts = $db->table('attachments')
                       ->where('visit_id', $v['id'])
                       ->orderBy('id', 'ASC')
                       ->get()
                       ->getResultArray();

            $out[] = [
                'id'         => (string) $v['id'],
                'date'       => $v['date'],
                'sequence'   => (int) $v['sequence'],
                'attachments'=> array_map(function ($a) {
                    return [
                        'id'        => (int) $a['id'],
                        'visit_id'  => (int) $a['visit_id'],
                        'type'      => $a['type'],
                        'filename'  => $a['filename'],
                        'filesize'  => (string) $a['filesize'],
                        'created_at'=> $a['created_at'],
                    ];
                }, $atts),
            ];
        }

        return $this->respond([
            'ok'      => true,
            'date'    => $today,
            'results' => $out,
        ]);
    }

    private function toBool($val): bool
    {
        if (is_bool($val)) return $val;
        $v = strtolower((string) $val);
        return in_array($v, ['1', 'true', 'yes', 'on'], true);
    }
}
