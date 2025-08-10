<?php namespace App\Controllers;

use App\Controllers\BaseController;

class PatientController extends BaseController
{
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
        return view('patient/review', [
            'query' => $q,
            'results' => []
        ]);
    }

    public function provisional()
    {
        $mobile = trim($this->request->getGet('mobile') ?? '');
        $unique = trim($this->request->getGet('uid') ?? '');
        return view('patient/print_provisional', ['mobile' => $mobile, 'uid' => $unique]);
    }
}
