<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminToolbar implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // In dev bypass mode, do nothing (let request through).
        if (env('DEV_NO_AUTH', false)) {
            return;
        }
        // Production behavior (no-op placeholder). Replace with real logic later.
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
