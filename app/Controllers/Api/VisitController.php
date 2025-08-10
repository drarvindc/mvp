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
    private function bad(int $code, string $msg){ return $this->response->setStatusCode($code)->setJSON(['ok'=>false,'error'=>$msg]); }

    private function visitKeyMode(): string
    {
        $fields = [];
        try { $fields = array_map('strtolower', $this->db->getFieldNames('visits')); } catch (\Throwable $e) {}
        if (in_array('unique_id', $fields, true)) return 'unique_id';
        if (in_array('uid',       $fields, true)) return 'uid';
        if (in_array('pet_id',    $fields, true)) return 'pet_id';
        return 'unique_id';
    }
    private function hasSequence(): bool
    {
        try { return in_array('sequence', array_map('strtolower', $this->db->getFieldNames('visits')), true); }
        catch (\Throwable $e) { return false; }
    }
    private function hasCreatedAt(): bool
    {
        try { return in_array('created_at', array_map('strtolower', $this->db->getFieldNames('visits')), true); }
        catch (\Throwable $e) { return false; }
    }

    private function ensureTodayVisit(string $uid, bool $forceNew = false): array
    {
        $today  = date('Y-m-d');
        $mode   = $this->visitKeyMode();
        $hasSeq = $this->hasSequence();
        $hasCA  = $this->hasCreatedAt();

        if (!$forceNew) {
            if ($mode === 'pet_id') {
                $petId = $this->db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRow('id');
                if ($petId) {
                    $q = $this->db->table('visits')->where(['pet_id'=>$petId,'visit_date'=>$today]);
                    $q = $hasSeq ? $q->orderBy('sequence','DESC') : ($hasCA ? $q->orderBy('created_at','DESC') : $q->orderBy('id','DESC'));
                    $row = $q->get()->getRowArray();
                    if ($row) return [$row, false];
                }
            } else {
                $q = $this->db->table('visits')->where([$mode=>$uid,'visit_date'=>$today]);
                $q = $hasSeq ? $q->orderBy('sequence','DESC') : ($hasCA ? $q->orderBy('created_at','DESC') : $q->orderBy('id','DESC'));
                $row = $q->get()->getRowArray();
                if ($row) return [$row, false];
            }
        }

        $insert = ['visit_date'=>$today];
        if ($this->hasCreatedAt()) $insert['created_at'] = Time::now($this->tz)->toDateTimeString();
        if ($mode === 'pet_id') {
            $insert['pet_id'] = $this->db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRow('id');
        } else {
            $insert[$mode] = $uid;
        }
        if ($hasSeq) {
            if ($mode === 'pet_id') {
                $maxSeq = (int)($this->db->table('visits')->selectMax('sequence','s')->where(['pet_id'=>$insert['pet_id'],'visit_date'=>$today])->get()->getRow('s') ?? 0);
            } else {
                $maxSeq = (int)($this->db->table('visits')->selectMax('sequence','s')->where([$mode=>$uid,'visit_date'=>$today])->get()->getRow('s') ?? 0);
            }
            $insert['sequence'] = $maxSeq + 1;
        }

        $this->db->table('visits')->insert($insert);
        $id  = $this->db->insertID();
        $row = $this->db->table('visits')->where('id',$id)->get()->getRowArray();
        return [$row, true];
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
                'id'       => $visit['id'],
                'date'     => $visit['visit_date'],
                'sequence' => $visit['sequence'] ?? 1,
                'unique_id'=> $uid,
                'wasCreated'=>$created,
            ],
            'pet'  => $basics['pet']   ?? null,
            'owner'=> $basics['owner'] ?? null,
        ]);
    }

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

        $y    = date('Y');
        $dmy  = date('dmy');
        $base = rtrim(ROOTPATH, '/')."/storage/patients/{$y}/{$uid}";
        if (!is_dir($base)) { @mkdir($base, 0775, true); }

        $mime = $file->getClientMimeType() ?: $file->getMimeType();

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
            'visit_id'   => $visit['id'],
            'type'       => $type,
            'filename'   => $candidate,
            'filesize'   => @filesize($path) ?: null,
            'mime'       => $mime ?: null,
            'note'       => $note ?: null,
            'created_at' => Time::now($this->tz)->toDateTimeString(),
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

    public function today()
    {
        $uid = trim((string)$this->request->getGet('uid'));
        if (!preg_match('/^\d{6}$/',$uid)) return $this->bad(400,'uid_required');

        $today  = date('Y-m-d');
        $mode   = $this->visitKeyMode();
        $hasSeq = $this->hasSequence();
        $hasCA  = $this->hasCreatedAt();

        if ($mode === 'pet_id') {
            $petId = $this->db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRow('id');
            $q = $this->db->table('visits')->where(['pet_id'=>$petId,'visit_date'=>$today]);
        } else {
            $q = $this->db->table('visits')->where([$mode=>$uid,'visit_date'=>$today]);
        }
        if     ($hasSeq) $q = $q->orderBy('sequence','DESC');
        elseif ($hasCA)  $q = $q->orderBy('created_at','DESC');
        else             $q = $q->orderBy('id','DESC');

        $visit = $q->get()->getRowArray();
        if (!$visit) return $this->ok(['ok'=>true,'visit'=>null,'attachments'=>[]]);

        $atts = $this->db->table('attachments')->where('visit_id',$visit['id'])->orderBy('id','ASC')->get()->getResultArray();
        return $this->ok([
            'ok'=>true,
            'visit'=>[
                'id'       => $visit['id'],
                'date'     => $visit['visit_date'],
                'sequence' => $visit['sequence'] ?? 1
            ],
            'attachments'=>$atts
        ]);
    }

    public function byDate()
    {
        $uid  = trim((string)$this->request->getGet('uid'));
        $date = trim((string)$this->request->getGet('date'));
        if (!preg_match('/^\d{6}$/',$uid)) return $this->bad(400,'uid_required');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) return $this->bad(400,'bad_date');

        $mode   = $this->visitKeyMode();
        $hasSeq = $this->hasSequence();
        $hasCA  = $this->hasCreatedAt();

        if ($mode === 'pet_id') {
            $petId = $this->db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRow('id');
            $q = $this->db->table('visits')->where(['pet_id'=>$petId,'visit_date'=>$date]);
        } else {
            $q = $this->db->table('visits')->where([$mode=>$uid,'visit_date'=>$date]);
        }
        if     ($hasSeq) $q = $q->orderBy('sequence','DESC');
        elseif ($hasCA)  $q = $q->orderBy('created_at','DESC');
        else             $q = $q->orderBy('id','DESC');

        $visit = $q->get()->getRowArray();
        if (!$visit) return $this->ok(['ok'=>true,'visit'=>null,'attachments'=>[]]);

        $atts = $this->db->table('attachments')->where('visit_id',$visit['id'])->orderBy('id','ASC')->get()->getResultArray();
        return $this->ok([
            'ok'=>true,
            'visit'=>[
                'id'       => $visit['id'],
                'date'     => $visit['visit_date'],
                'sequence' => $visit['sequence'] ?? 1
            ],
            'attachments'=>$atts
        ]);
    }
}
