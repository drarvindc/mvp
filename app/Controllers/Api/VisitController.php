<?php namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\VisitsModel;
use App\Models\OwnersModel;
use App\Models\PetsModel;

class VisitController extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    /**
     * Opens a visit for the given UID if one doesn't exist today.
     */
    public function open()
    {
        $uid = $this->request->getJSON()->uid ?? null;
        if (!$uid) {
            return $this->failValidationError('UID is required');
        }

        $visits = new VisitsModel();
        $visit = $visits->findTodayByUid($uid);

        if (!$visit) {
            $visitId = $visits->insert([
                'unique_id' => $uid,
                'visit_date' => date('Y-m-d'),
                'status'     => 'open'
            ]);
            $visit = $visits->find($visitId);
        }

        return $this->respond(['ok' => true, 'visit' => $visit]);
    }

    /**
     * Uploads a file and associates it with today's visit for a UID.
     */
    public function upload()
    {
        $uid  = $this->request->getPost('uid');
        $type = $this->request->getPost('type') ?? 'doc';
        $note = $this->request->getPost('note');

        if (!$uid) {
            return $this->failValidationError('UID is required');
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->failValidationError('Valid file is required');
        }

        // Ensure patient storage folder
        $year   = date('Y');
        $base   = WRITEPATH . 'patients/' . $year . '/' . $uid;
        if (!is_dir($base)) {
            mkdir($base, 0775, true);
        }

        // Build unique filename: DDMMYY-type-UID(-seq).ext
        $dmy    = date('dmy');
        $ext    = $file->getExtension();
        $candidate = $dmy . '-' . $type . '-' . $uid . '.' . $ext;
        $seq    = 0;
        while (file_exists($base . '/' . $candidate)) {
            $seq++;
            $suffix = str_pad((string)$seq, 2, '0', STR_PAD_LEFT);
            $candidate = $dmy . '-' . $type . '-' . $uid . '-' . $suffix . '.' . $ext;
        }

        $path = $base . '/' . $candidate;
        $file->move($base, $candidate);

        // Ensure today's visit
        $visits = new VisitsModel();
        $visit  = $visits->findTodayByUid($uid);
        if (!$visit) {
            $visitId = $visits->insert([
                'unique_id' => $uid,
                'visit_date' => date('Y-m-d'),
                'status'     => 'open'
            ]);
            $visit = $visits->find($visitId);
        }

        // Save attachment record
        $db = \Config\Database::connect();
        $db->table('visit_files')->insert([
            'visit_id' => $visit['id'],
            'file_path' => 'patients/' . $year . '/' . $uid . '/' . $candidate,
            'file_type' => $type,
            'note'      => $note,
            'uploaded_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'ok'   => true,
            'path' => $path,
            'visit' => $visit
        ]);
    }

    /**
     * Returns all files for today's visit of a UID.
     */
    public function today()
    {
        $uid = $this->request->getGet('uid');
        if (!$uid) {
            return $this->failValidationError('UID is required');
        }

        $visits = new VisitsModel();
        $visit  = $visits->findTodayByUid($uid);
        if (!$visit) {
            return $this->respond(['ok' => false, 'error' => 'No visit today']);
        }

        $db   = \Config\Database::connect();
        $files = $db->table('visit_files')
            ->where('visit_id', $visit['id'])
            ->get()
            ->getResultArray();

        return $this->respond([
            'ok' => true,
            'visit' => $visit,
            'files' => $files
        ]);
    }
}
