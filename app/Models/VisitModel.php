<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitModel extends Model
{
    protected $table      = 'visits';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pet_id','visit_date','sequence','visit_seq','status','created_at','updated_at'];

    public function latestForPet(int $petId, string $date)
    {
        return $this->where(['pet_id'=>$petId,'visit_date'=>$date])
                    ->orderBy('sequence','DESC')
                    ->first();
    }

    public function allForDate(int $petId, string $date): array
    {
        return $this->where(['pet_id'=>$petId,'visit_date'=>$date])
                    ->orderBy('sequence','ASC')
                    ->findAll();
    }
}
