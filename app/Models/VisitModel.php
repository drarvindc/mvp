<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitModel extends Model
{
    protected $table         = 'visits';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['patient_id', 'date', 'sequence', 'created_at', 'updated_at'];
    protected $useTimestamps = false;

    public function latestFor($patientId, string $date)
    {
        return $this->where(['patient_id' => $patientId, 'date' => $date])
                    ->orderBy('sequence', 'DESC')
                    ->first();
    }

    public function allForDate($patientId, string $date): array
    {
        return $this->where(['patient_id' => $patientId, 'date' => $date])
                    ->orderBy('sequence', 'ASC')
                    ->findAll();
    }
}
