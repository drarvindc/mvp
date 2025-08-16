<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DevOpenAccess implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Toggle via .env -> DEV_NO_AUTH=true
        $dev = env('DEV_NO_AUTH', false);
        if (! $dev) {
            return;
        }

        // 1) Ensure admin session so admin pages render without login
        $session = session();
        if ($session && ! $session->get('admin_id')) {
            $session->set('admin_id', 2);      // Use any valid admin user id
            $session->set('admin_name', 'Dev Mode');
            $session->set('admin_role', 'admin');
        }

        // 2) Auto-inject API token for tester/API calls if not provided
        $token = env('ANDROID_API_TOKEN');
        if ($token && empty($_GET['token']) && empty($_REQUEST['token'])) {
            $_GET['token'] = $token;
            $_REQUEST['token'] = $token;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
