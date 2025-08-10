<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $key   = trim((string)$request->getGet('key'));
        $valid = getenv('MIGRATE_WEB_KEY'); 
        $valid = $valid !== false ? trim((string)$valid) : '';

        if ($key !== '' && $valid !== '' && hash_equals($valid, $key)) {
            return; // allowed
        }
        // If you also support admin sessions, you can allow: if (session('role') === 'admin') return;

        return redirect()->to('/'); // unauthorized → home
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
