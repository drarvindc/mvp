<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $key = $request->getGet('key') ?? '';
        $validKey = getenv('MIGRATE_WEB_KEY');
        if (!$validKey || $key !== $validKey) {
            return redirect()->to('/'); // unauthorized → home
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
