<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminBypassKey implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if ($session && $session->get('admin_id')) {
            $key = env('MIGRATE_WEB_KEY');
            if ($key) {
                $_GET['key'] = $key;
                $_REQUEST['key'] = $key;
            }
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
