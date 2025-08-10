<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session('role');
        $envToken = getenv('MIGRATE_WEB_KEY') ?: '';
        $key = $request->getGet('key');
        if ($role === 'admin') return;
        if ($envToken && $key === $envToken) return;
        return redirect()->to('/')->with('error','Unauthorized');
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
