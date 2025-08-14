<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Allow the public auth endpoints and one-time bootstrap
        $uri = $request->getUri();
        $path = ltrim($uri->getPath(), '/');

        $public = [
            'admin/login',
            'admin/logout',
            'admin/tools/make-admin',   // key-guarded
        ];

        foreach ($public as $p) {
            if (stripos($path, $p) === 0) {
                return; // allow through
            }
        }

        $session = session();
        if ($session->get('admin_id')) {
            return; // already authenticated
        }

        // redirect to login, preserving intended URL
        return redirect()->to(site_url('admin/login') . '?redirect=' . rawurlencode((string)$uri));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
