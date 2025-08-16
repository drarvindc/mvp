<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DevOpenAccess implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! env('DEV_NO_AUTH', false)) {
            return;
        }

        // Ensure an admin session for admin routes
        $session = session();
        if ($session && ! $session->get('admin_id')) {
            $session->set('admin_id', 1);
            $session->set('admin_name', 'Dev Mode');
            $session->set('admin_role', 'admin');
        }

        // Inject key/token into CI Request so $this->request->getGet() sees them
        $get = $request->getGet();
        $changed = false;

        $webKey = env('MIGRATE_WEB_KEY');
        if ($webKey && empty($get['key'])) {
            $get['key'] = $webKey;
            $changed = true;
        }

        $apiToken = env('ANDROID_API_TOKEN');
        if ($apiToken && empty($get['token'])) {
            $get['token'] = $apiToken;
            $changed = true;
        }

        if ($changed) {
            $request->setGlobal('get', $get);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
