<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Accept either an admin session OR the MIGRATE_WEB_KEY query/header for headless environments
        $sessionRole = session('role');
        $envToken    = getenv('MIGRATE_WEB_KEY') ?: '';
        $queryToken  = $request->getGet('key');
        $headerToken = $request->getHeaderLine('X-Migrate-Key');

        if ($sessionRole === 'admin') {
            return;
        }
        if ($envToken && ($queryToken === $envToken || $headerToken === $envToken)) {
            return;
        }
        return redirect()->to('/'); // block if not authorized
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
