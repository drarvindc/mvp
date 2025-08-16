<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DevOpenAccess implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $dev = env('DEV_NO_AUTH', false);
        if (! $dev) { return; }

        $session = session();
        if ($session && ! $session->get('admin_id')) {
            $session->set('admin_id', 2);
            $session->set('admin_name', 'Dev Mode');
            $session->set('admin_role', 'admin');
        }

        $webKey = env('MIGRATE_WEB_KEY');
        if ($webKey && empty($_GET['key']) && empty($_REQUEST['key'])) {
            $_GET['key'] = $webKey;
            $_REQUEST['key'] = $webKey;
        }

        $apiToken = env('ANDROID_API_TOKEN');
        if ($apiToken && empty($_GET['token']) && empty($_REQUEST['token'])) {
            $_GET['token'] = $apiToken;
            $_REQUEST['token'] = $apiToken;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
