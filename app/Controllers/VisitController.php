<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class VisitController extends ResourceController
{
    protected $format = 'json';

    public function today()
    {
        $uid = trim((string)$this->request->getVar('uid'));
        $all = $this->toBool($this->request->getVar('all'));

        if ($uid === '') {
            return $this->failValidationErrors('Missing uid');
        }

        $db = \Config\Database::connect();
        $pet = $db->table('pets')->select('id')->where('unique_id',$uid)->get()->getRowArray();
        if (!$pet) {
            return $this->respond(['ok'=>false,'error'=>'Pet not found'],404);
        }
        $petId = (int)$pet['id'];
        $today = date('Y-m-d');

        if ($all) {
            $visits = $db->table('visits')->where(['pet_id'=>$petId,'visit_date'=>$today])->orderBy('sequence','ASC')->get()->getResultArray();
        } else {
            $visits = $db->table('visits')->where(['pet_id'=>$petId,'visit_date'=>$today])->orderBy('sequence','DESC')->get(1,0)->getResultArray();
        }

        $out = [];
        foreach ($visits as $v) {
            $atts = $db->table('attachments')->where('visit_id',$v['id'])->orderBy('id','ASC')->get()->getResultArray();
            $out[] = [
                'id'         => (string)$v['id'],
                'date'       => $v['visit_date'],
                'sequence'   => (int)$v['sequence'],
                'attachments'=> $atts
            ];
        }

        return $this->respond([
            'ok'=>true,
            'date'=>$today,
            'results'=>$out
        ]);
    }

    private function toBool($val): bool
    {
        if (is_bool($val)) return $val;
        $v = strtolower((string)$val);
        return in_array($v, ['1','true','yes','on'], true);
    }
}
