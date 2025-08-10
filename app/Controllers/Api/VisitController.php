<?php namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class VisitController extends ResourceController
{
    protected $format = 'json';

    public function open($uid = null)
    {
        if (!$this->checkAuth()) {
            return $this->failUnauthorized('Invalid token');
        }

        $db = \Config\Database::connect();
        $pet = $db->table('pets')->where('unique_id', $uid)->get()->getRow();

        if (!$pet) {
            return $this->failNotFound("Pet with UID $uid not found");
        }

        $today = date('Y-m-d');
        $visit = $db->table('visits')
            ->where('pet_id', $pet->id)
            ->where('visit_date', $today)
            ->get()
            ->getRow();

        if (!$visit) {
            $db->table('visits')->insert([
                'pet_id' => $pet->id,
                'visit_date' => $today,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $visitId = $db->insertID();
        } else {
            $visitId = $visit->id;
        }

        return $this->respond([
            'ok' => true,
            'visit_id' => $visitId,
            'pet' => $pet
        ]);
    }

    public function upload()
    {
        if (!$this->checkAuth()) {
            return $this->failUnauthorized('Invalid token');
        }

        $uid  = $this->request->getPost('uid');
        $type = $this->request->getPost('type') ?? 'doc';
        $file = $this->request->getFile('file');

        if (!$uid || !$file->isValid()) {
            return $this->failValidationError('UID and valid file required');
        }

        $db = \Config\Database::connect();
        $pet = $db->table('pets')->where('unique_id', $uid)->get()->getRow();
        if (!$pet) {
            return $this->failNotFound("Pet with UID $uid not found");
        }

        // Ensure visit exists
        $today = date('Y-m-d');
        $visit = $db->table('visits')
            ->where('pet_id', $pet->id)
            ->where('visit_date', $today)
            ->get()
            ->getRow();

        if (!$visit) {
            $db->table('visits')->insert([
                'pet_id' => $pet->id,
                'visit_date' => $today,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $visitId = $db->insertID();
        } else {
            $visitId = $visit->id;
        }

        // Build storage path
        $year = date('Y');
        $baseDir = WRITEPATH . "storage/patients/$year/$uid";
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        $dmy = date('dmY');
        $ext = $file->getExtension();
        $seq = 0;
        $candidate = "$dmy-$type-$uid.$ext";

        while (file_exists("$baseDir/$candidate")) {
            $seq++;
            $suffix = str_pad((string)$seq, 2, '0', STR_PAD_LEFT);
            $candidate = "$dmy-$type-$uid-$suffix.$ext";
        }

        $file->move($baseDir, $candidate);

        // Record in DB
        $db->table('visit_files')->insert([
            'visit_id' => $visitId,
            'file_type' => $type,
            'file_path' => "$year/$uid/$candidate",
            'uploaded_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'ok' => true,
            'file' => $candidate,
            'visit_id' => $visitId
        ]);
    }

    public function today($uid = null)
    {
        if (!$this->checkAuth()) {
            return $this->failUnauthorized('Invalid token');
        }

        $db = \Config\Database::connect();
        $pet = $db->table('pets')->where('unique_id', $uid)->get()->getRow();
        if (!$pet) {
            return $this->failNotFound("Pet with UID $uid not found");
        }

        $today = date('Y-m-d');
        $visit = $db->table('visits')
            ->where('pet_id', $pet->id)
            ->where('visit_date', $today)
            ->get()
            ->getRow();

        if (!$visit) {
            return $this->respond(['ok' => true, 'files' => []]);
        }

        $files = $db->table('visit_files')
            ->where('visit_id', $visit->id)
            ->get()
            ->getResult();

        return $this->respond([
            'ok' => true,
            'files' => $files
        ]);
    }

    private function checkAuth(): bool
    {
        $token = $this->request->getHeaderLine('X-Api-Key') ?: $this->request->getGetPost('token');
        return $token && $token === getenv('ANDROID_API_TOKEN');
    }
}
