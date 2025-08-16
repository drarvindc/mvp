<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DmyDate implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (env('DEV_NO_AUTH', false)) {
            return; // allow through in dev
        }
        // production logic can be added later
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
