<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class StableApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $envToken = env('ANDROID_API_TOKEN');
        if (!$envToken) {
            return service('response')->setStatusCode(500)->setJSON(['ok'=>false,'error'=>'server_token_missing']);
        }

        $auth = $request->getHeaderLine('Authorization');
        $token = '';
        if (stripos($auth, 'Bearer ') === 0) {
            $token = trim(substr($auth, 7));
        } else {
            $token = (string) $request->getGet('token') ?: (string) $request->getPost('token');
        }

        if (!$token || !hash_equals($envToken, $token)) {
            return service('response')->setStatusCode(401)->setJSON(['ok'=>false,'error'=>'unauthorized']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
