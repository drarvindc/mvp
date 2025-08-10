<?php namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;

class PatientController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        date_default_timezone_set('Asia/Kolkata');
    }

    public function intake()
    {
        return view('patient/search');
    }

    public function find()
    {
        $q = trim($this->request->getPost('q') ?? '');
        if ($q === '') {
            return redirect()->back()->with('error','Please enter a mobile number or Unique ID.');
        }
        $isUid = preg_match('/^\d{6}$/', $q) === 1;

        if ($isUid) {
            $pet = $this->db->table('pets p')
                ->select('p.id as pet_id, p.unique_id, p.pet_name, p.gender, p.status, p.owner_id, p.species_id, p.breed_id, o.first_name, o.last_name, s.name as species, b.name as breed')
                ->join('owners o','o.id = p.owner_id','left')
                ->join('species s','s.id = p.species_id','left')
                ->join('breeds b','b.id = p.breed_id','left')
                ->where('p.unique_id', $q)->get()->getRowArray();

            $results = [];
            if ($pet) {
                $ownerId = (int)$pet['owner_id'];
                $results = $this->db->table('pets p')
                    ->select('p.id as pet_id, p.unique_id, p.pet_name, p.gender, p.status, p.owner_id, p.species_id, p.breed_id, s.name as species, b.name as breed')
                    ->join('species s','s.id = p.species_id','left')
                    ->join('breeds b','b.id = p.breed_id','left')
                    ->where('p.owner_id', $ownerId)->get()->getResultArray();
            }

            return view('patient/review', [
                'query'   => $q,
                'mode'    => 'uid',
                'petHit'  => $pet,
                'results' => $results
            ]);
        }

        // MOBILE MODE (assumes storage without country code)
        $digits = preg_replace('/\D+/', '', $q);
        if ($digits === '') {
            return redirect()->back()->with('error','Invalid mobile format.');
        }
        $last10 = substr($digits, -10);

        $results = $this->db->table('pets p')
            ->select('p.id as pet_id, p.unique_id, p.pet_name, p.gender, p.status, p.owner_id, p.species_id, p.breed_id, o.first_name, o.last_name, s.name as species, b.name as breed')
            ->join('owners o','o.id = p.owner_id','left')
            ->join('owner_mobiles m','m.owner_id = o.id','left')
            ->join('species s','s.id = p.species_id','left')
            ->join('breeds b','b.id = p.breed_id','left')
            ->where('m.mobile_e164', $last10)
            ->groupBy('p.id')
            ->get()->getResultArray();

        return view('patient/review', [
            'query'   => $q,
            'mode'    => 'mobile',
            'petHit'  => null,
            'results' => $results,
            'digits'  => $last10
        ]);
    }

    public function printExisting()
    {
        $uid = trim($this->request->getGet('uid') ?? '');
        if (!preg_match('/^\d{6}$/',$uid)) {
            return redirect()->to('patient/intake')->with('error','Invalid Unique ID.');
        }
        $pet = $this->db->table('pets p')
            ->select('p.unique_id, p.pet_name, o.first_name, o.last_name')
            ->join('owners o','o.id = p.owner_id','left')
            ->where('p.unique_id',$uid)->get()->getRowArray();
        if (!$pet) {
            return redirect()->to('patient/intake')->with('error','Pet not found.');
        }
        return view('patient/print_provisional', [
            'uid'    => $pet['unique_id'],
            'mobile' => '',
            'pet'    => $pet
        ]);
    }

    public function provisionalCreate()
    {
        $mobileRaw = trim($this->request->getPost('mobile') ?? '');
        $digits = preg_replace('/\D+/', '', $mobileRaw);
        if ($digits === '') {
            return redirect()->back()->with('error','Enter a valid mobile number.');
        }

        $this->db->transStart();

        $yearTwo = date('y');
        $counterRow = $this->db->table('year_counters')->where('year_two', $yearTwo)->get()->getRowArray();
        if (!$counterRow) {
            $this->db->table('year_counters')->insert([
                'year_two' => $yearTwo,
                'last_seq' => 0,
                'updated_at' => Time::now('Asia/Kolkata')->toDateTimeString()
            ]);
            $counterRow = $this->db->table('year_counters')->where('year_two', $yearTwo)->get()->getRowArray();
        }

        $this->db->query('UPDATE year_counters SET last_seq = last_seq + 1, updated_at = ? WHERE id = ?', [
            Time::now('Asia/Kolkata')->toDateTimeString(),
            $counterRow['id']
        ]);
        $counterRow = $this->db->table('year_counters')->where('id', $counterRow['id'])->get()->getRowArray();
        $seq = (int)$counterRow['last_seq'];
        $uid = $yearTwo . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);

        $exist = $this->db->table('owner_mobiles')->select('owner_id')->where('mobile_e164', substr($digits,-10))->get()->getRowArray();
        if ($exist) {
            $ownerId = (int)$exist['owner_id'];
        } else {
            $this->db->table('owners')->insert([
                'first_name' => '',
                'middle_name'=> null,
                'last_name'  => '',
                'email'      => null,
                'locality'   => null,
                'address'    => null,
                'status'     => 'provisional',
                'created_at' => Time::now('Asia/Kolkata')->toDateTimeString(),
                'updated_at' => Time::now('Asia/Kolkata')->toDateTimeString(),
            ]);
            $ownerId = $this->db->insertID();

            $this->db->table('owner_mobiles')->insert([
                'owner_id'   => $ownerId,
                'mobile_e164'=> substr($digits,-10),
                'is_primary' => 1,
                'is_verified'=> 0,
                'created_at' => Time::now('Asia/Kolkata')->toDateTimeString(),
            ]);
        }

        $this->db->table('pets')->insert([
            'owner_id'   => $ownerId,
            'unique_id'  => $uid,
            'pet_name'   => null,
            'species_id' => null,
            'breed_id'   => null,
            'gender'     => 'unknown',
            'dob'        => null,
            'age_years'  => null,
            'age_months' => null,
            'color'      => null,
            'microchip'  => null,
            'notes'      => null,
            'status'     => 'provisional',
            'created_at' => Time::now('Asia/Kolkata')->toDateTimeString(),
            'updated_at' => Time::now('Asia/Kolkata')->toDateTimeString(),
        ]);

        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return redirect()->back()->with('error','Could not create provisional record.');
        }

        return view('patient/print_provisional', [
            'uid'    => $uid,
            'mobile' => substr($digits,-10),
            'pet'    => null
        ]);
    }

    public function provisional()
    {
        $mobile = trim($this->request->getGet('mobile') ?? '');
        $unique = trim($this->request->getGet('uid') ?? '');
        return view('patient/print_provisional', ['mobile' => $mobile, 'uid' => $unique]);
    }
}
