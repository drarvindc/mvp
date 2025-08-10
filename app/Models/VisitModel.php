<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitModel extends Model
{
    protected $table         = 'visits';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['pet_id','visit_date','visit_seq','status','source','reason','remarks','next_visit','created_at','updated_at'];
    protected $useTimestamps = false;

    public function latestFor($petId, string $date)
    {
        return $this->where(['pet_id'=>$petId,'visit_date'=>$date])
                    ->orderBy('visit_seq','DESC')
                    ->first();
    }

    public function allForDate($petId, string $date): array
    {
        return $this->where(['pet_id'=>$petId,'visit_date'=>$date])
                    ->orderBy('visit_seq','ASC')
                    ->findAll();
    }
}
