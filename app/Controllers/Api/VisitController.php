<?php namespace App\Controllers\Api;

use App\Controllers\BaseController;

class VisitController extends BaseController
{
    protected $db;
    protected $tz = 'Asia/Kolkata';

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        date_default_timezone_set($this->tz);
        helper(['filesystem']);
    }

    private function ok($data){ return $this->response->setJSON($data); }
    private function bad(int $code, string $msg){ return $this->response->setStatusCode($code)->setJSON(['ok'=>false,'error'=>$msg]); }

    private function getPetIdByUid(string $uid)
    {
        return $this->db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRow('id');
    }

    // GET /api/visit/today?uid=XXXX&date=YYYY-MM-DD[&all=1]
    public function today()
    {
        $uid  = trim((string) $this->request->getGet('uid'));
        $date = trim((string) ($this->request->getGet('date') ?: date('Y-m-d')));
        $all  = in_array(strtolower((string)$this->request->getGet('all')), ['1','true','yes','on'], true);

        if ($uid === '') return $this->bad(422, 'Missing uid');

        $petId = $this->getPetIdByUid($uid);
        if (!$petId) return $this->ok(['ok'=>true,'date'=>$date,'results'=>[]]);

        $q = $this->db->table('visits')->where(['pet_id'=>$petId,'visit_date'=>$date])->orderBy('visit_seq','ASC');
        $visits = $all ? $q->get()->getResultArray() : ($this->db->table('visits')->where(['pet_id'=>$petId,'visit_date'=>$date])->orderBy('visit_seq','DESC')->get(1)->getResultArray());

        $out = [];
        foreach ($visits as $v) {
            if (!$v) continue;
            $atts = $this->db->table('documents')->where('visit_id',$v['id'])->orderBy('id','ASC')->get()->getResultArray();
            $out[] = [
                'id' => (string)$v['id'],
                'date' => $v['visit_date'],
                'sequence' => (int)$v['visit_seq'],
                'attachments' => array_map(function($a){
                    return [
                        'id' => (int)$a['id'],
                        'visit_id' => (int)($a['visit_id'] ?? 0),
                        'type' => (string)($a['type'] ?? ''),
                        'filename' => (string)($a['filename'] ?? ''),
                        'filesize' => (string)($a['size_bytes'] ?? ''),
                        'created_at' => (string)($a['created_at'] ?? ''),
                        'path' => (string)($a['path'] ?? '')
                    ];
                }, $atts)
            ];
        }

        return $this->ok(['ok'=>true,'date'=>$date,'results'=>$out]);
    }

    // POST /api/visit/open  (uid, forceNewVisit=1)
    public function open()
    {
        $uid   = trim((string) $this->request->getPost('uid'));
        $force = in_array(strtolower((string)$this->request->getPost('forceNewVisit')), ['1','true','yes','on'], true);
        if ($uid === '') return $this->bad(422, 'Missing uid');

        $petId = $this->getPetIdByUid($uid);
        if (!$petId) return $this->bad(404, 'Pet not found for uid');

        $today = date('Y-m-d');
        $this->db->transStart();
        // lock to compute next seq
        $row = $this->db->query('SELECT COALESCE(MAX(visit_seq),0) last FROM visits WHERE pet_id=? AND visit_date=? FOR UPDATE', [$petId,$today])->getRowArray();
        $next = (int)($row['last'] ?? 0);

        if (!$force && $next > 0) {
            // return latest
            $visit = $this->db->table('visits')->where(['pet_id'=>$petId,'visit_date'=>$today])->orderBy('visit_seq','DESC')->get(1)->getRowArray();
            $this->db->transComplete();
            return $this->ok(['ok'=>true,'visit'=>['id'=>(string)$visit['id'],'date'=>$visit['visit_date'],'sequence'=>(int)$visit['visit_seq']]]);
        }

        $next = $next + 1;
        $this->db->table('visits')->insert([
            'pet_id' => $petId,
            'visit_date' => $today,
            'visit_seq' => $next,
            'status' => 'open',
            'source' => 'web',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db->insertID();
        $this->db->transComplete();

        if (!$this->db->transStatus()) return $this->bad(500,'DB transaction failed');

        return $this->ok(['ok'=>true,'visit'=>['id'=>(string)$id,'date'=>$today,'sequence'=>$next]]);
    }
}
