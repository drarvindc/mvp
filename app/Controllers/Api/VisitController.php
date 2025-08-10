<?php namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;

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
    private function bad($code, $msg){ return $this->response->setStatusCode($code)->setJSON(['ok'=>false,'error'=>$msg]); }

    /** Figure out how visits table stores the patient reference. */
    private function visitKeyMode(): string
    {
        // returns: 'uid' | 'unique_id' | 'pet_id'
        $fields = array_map('strtolower', $this->db->getFieldNames('visits'));
        if (in_array('unique_id', $fields, true)) return 'unique_id';
        if (in_array('uid', $fields, true))       return 'uid';
        if (in_array('pet_id', $fields, true))    return 'pet_id';
        // fallback to uid
        return 'uid';
    }

    /** Get (or create) today's visit for a UID; respects schema differences. */
    private function ensureTodayVisit(string $uid, bool $forceNew = false): array
    {
        $today = date('Y-m-d');
        $mode  = $this->visitKeyMode();

        // find existing
        if (!$forceNew) {
            if ($mode === 'pet_id') {
                $petId = $this->db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRow('id');
                if ($petId) {
                    $row = $this->db->table('visits')
                        ->where(['pet_id'=>$petId,'visit_date'=>$today])
                        ->orderBy('sequence','DESC')->get()->getRowArray();
                    if ($row) return [$row,false];
                }
            } else {
                $row = $this->db->table('visits')
                    ->where([$mode=>$uid,'visit_date'=>$today])
                    ->orderBy('sequence','DESC')->get()->getRowArray();
                if ($row) return [$row,false];
            }
        }

        // compute sequence
        if ($mode === 'pet_id') {
            $petId = $this->db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRow('id');
            $maxSeq = (int)($this->db->table('visits')->selectMax('sequence','s')
                ->where(['pet_id'=>$petId,'visit_date'=>$today])->get()->getRow('s') ?? 0);
            $seq = $maxSeq + 1;
            $this->db->table('visits')->insert([
                'pet_id'    => $petId,
                'visit_date'=> $today,
                'sequence'  => $seq,
                'created_at'=> Time::now($this->tz)->toDateTimeString(),
            ]);
        } else {
            $maxSeq = (int)($this->db->table('visits')->selectMax('sequence','s')
                ->where([$mode=>$uid,'visit_date'=>$today])->get()->getRow('s') ?? 0);
            $seq = $maxSeq + 1;
            $this->db->table('visits')->insert([
                $mode       => $uid,
                'visit_date'=> $today,
                'sequence'  => $seq,
                'created_at'=> Time::now($this->tz)->toDateTimeString(),
            ]);
        }

        $id  = $this->db->insertID();
        $row = $this->db->table('visits')->where('id',$id)->get()->getRowArray();
        return [$row,true];
    }

    private function getPetOwnerBasics(string $uid): array
    {
        $pet = $this->db->table('pets p')
            ->select('p.unique_id, p.pet_name, s.name as species, b.name as breed, o.first_name, o.last_name')
            ->join('owners o','o.id = p.owner_id','left')
            ->join('species s','s.id = p.species_id','left')
            ->join('breeds b','b.id = p.breed_id','left')
            ->where('p.unique_id',$uid)->get()->getRowArray();
        if (!$pet) return [];

        $ownerId = $this->db->table('pets')->select('owner_id')->where('unique_id',$uid)->get()->getRow('owner_id');
        $mobile  = $this->db->table('owner_mobiles')->select('mobile_e164')->where(['owner_id'=>$ownerId,'is_primary'=>1])->get()->getRow('mobile_e164');

        return [
            'pet'=>[
                'unique_id'=>$pet['unique_id'],
                'name'=>$pet['pet_name'] ?? null,
                'species'=>$pet['species'] ?? null,
                'breed'=>$pet['breed'] ?? null,
            ],
            'owner'=>[
                'name'=>trim(($pet['first_name']??'').' '.($pet['last_name']??'')) ?: null,
                'mobile'=>$mobile ?: null
            ]
        ];
    }

    // POST /api/visit/open  JSON: { "uid": "250001" }
    public function open()
    {
        $json = $this->request->getJSON(true);
        $uid  = trim($json['uid'] ?? '');
        if (!preg_match('/^\d{6}$/',$uid)) return $this->bad(400,'uid_required');

        $exists = $this->db->table('pets')->select('unique_id')->where('unique_id',$uid)->get()->getRowArray();
        if (!$exists) return $this->bad(404,'uid_not_found');

        [$visit, $created] = $this->ensureTodayVisit($uid, false);
        $basics = $this->getPetOwnerBasics($uid);

        return $this->ok([
            'ok'=>true,
            'visit'=>[
                'id'=>$visit['id'],
                'date'=>$visit['visit_date'],
                'sequence'=>$visit['sequence'],
                // echo the uid for convenience regardless of column used
                'unique_id'=>$uid,
                'wasCreated'=>$created,
            ],
            'pet'=>$basics['pet'] ?? null,
            'owner'=>$basics['owner'] ?? null,
        ]);
    }

    // POST /api/visit/upload  multipart: uid, type, file, note?, forceNewVisit?
    public function upload()
    {
        $uid   = trim((string)$this->request->getPost('uid'));
        $type  = strtolower(trim((string)$this->request->getPost('type')));
        $note  = trim((string)$this->request->getPost('note'));
        $force = filter_var($this->request->getPost('forceNewVisit'), FILTER_VALIDATE_BOOLEAN);

        if (!preg_match('/^\d{6}$/',$uid)) return $this->bad(400,'uid_required');
        $allowedTypes = ['rx','photo','doc','xray','lab','usg','invoice'];
        if (!in_array($type, $allowedTypes, true)) return $this->bad(415,'unsupported_type');

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) return $this->bad(400,'file_required');

        $ext = strtolower($file->getExtension());
        $allowedExt = ['jpg','jpeg','png','pdf','webp'];
        if (!in_array($ext,$allowedExt,true)) return $this->bad(415,'unsupported_media_type');
        if ($file->getSize() > 10*1024*1024) return $this->bad(413,'file_too_large');

        $exists = $this->db->table('pets')->select('unique_id')->where('unique_id',$uid)->get()->getRowArray();
        if (!$exists) return $this->bad(404,'uid_not_found');

        [$visit, $created] = $this->ensureTodayVisit($uid, $force);

        // /storage/patients/{YYYY}/{UID}/
        $y    = date('Y');
        $dmy  = date('dmy');
        $base = rtrim(ROOTPATH, '/')."/storage/patients/{$y}/{$uid}";
        if (!is_dir($base)) { @mkdir($base, 0775, true); }

        $seq = 1;
        $candidate = "{$dmy}-{$type}-{$uid}.{$ext}";
        while (file_exists("{$base}/{$candidate}")) {
            $seq++;
            $suffix = str_pad((string)$seq, 2, '0', STR_PAD_LEFT);
            $candidate = "{$dmy}-{$type}-{$uid}-{$suffix}.{$ext}";
        }
        $path = "{$base}/{$candidate}";

        if (!$file->move($base, $candidate, true)) {
            return $this->bad(500,'upload_failed');
        }

        $this->db->table('attachments')->insert([
            'visit_id'  => $visit['id'],
            'type'      => $type,
            'filename'  => $candidate,
            'filesize'  => @filesize($path) ?: null,
            'mime'      => $file->getMimeType(),
            'note'      => $note ?: null,
            'created_at'=> Time::now($this->tz)->toDateTimeString(),
        ]);
        $attId = $this->db->insertID();

        return $this->response->setStatusCode(201)->setJSON([
            'ok'=>true,
            'visitId'=>$visit['id'],
            'attachment'=>[
                'id'=>$attId,
                'type'=>$type,
                'filename'=>$candidate,
                'url'=> site_url("admin/visit/file?id={$attId}"),
                'created_at'=> Time::now($this->tz)->toDateTimeString(),
            ]
        ]);
    }

    // GET /api/visit/today?uid=250001
    public function today()
    {
        $uid = trim((string)$this->request->getGet('uid'));
        if (!preg_match('/^\d{6}$/',$uid)) return $this->bad(400,'uid_required');

        $today = date('Y-m-d');
        $mode  = $this->visitKeyMode();

        if ($mode === 'pet_id') {
            $petId = $this->db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRow('id');
            $visit = $this->db->table('visits')
                ->where(['pet_id'=>$petId,'visit_date'=>$today])
                ->orderBy('sequence','DESC')->get()->getRowArray();
        } else {
            $visit = $this->db->table('visits')
                ->where([$mode=>$uid,'visit_date'=>$today])
                ->orderBy('sequence','DESC')->get()->getRowArray();
        }
        if (!$visit) return $this->ok(['ok'=>true,'visit'=>null,'attachments'=>[]]);

        $atts = $this->db->table('attachments')->where('visit_id',$visit['id'])->orderBy('id','ASC')->get()->getResultArray();
        return $this->ok([
            'ok'=>true,
            'visit'=>['id'=>$visit['id'],'date'=>$visit['visit_date'],'sequence'=>$visit['sequence']],
            'attachments'=>$atts
        ]);
    }

    // GET /api/visit/by-date?uid=250001&date=YYYY-MM-DD
    public function byDate()
    {
        $uid  = trim((string)$this->request->getGet('uid'));
        $date = trim((string)$this->request->getGet('date'));
        if (!preg_match('/^\d{6}$/',$uid)) return $this->bad(400,'uid_required');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) return $this->bad(400,'bad_date');

        $mode  = $this->visitKeyMode();
        if ($mode === 'pet_id') {
            $petId = $this->db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRow('id');
            $visit = $this->db->table('visits')
                ->where(['pet_id'=>$petId,'visit_date'=>$date])
                ->orderBy('sequence','DESC')->get()->getRowArray();
        } else {
            $visit = $this->db->table('visits')
                ->where([$mode=>$uid,'visit_date'=>$date])
                ->orderBy('sequence','DESC')->get()->getRowArray();
        }
        if (!$visit) return $this->ok(['ok'=>true,'visit'=>null,'attachments'=>[]]);

        $atts = $this->db->table('attachments')->where('visit_id',$visit['id'])->orderBy('id','ASC')->get()->getResultArray();
        return $this->ok([
            'ok'=>true,
            'visit'=>['id'=>$visit['id'],'date'=>$visit['visit_date'],'sequence'=>$visit['sequence']],
            'attachments'=>$atts
        ]);
    }
}
