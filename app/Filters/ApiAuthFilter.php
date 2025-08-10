<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = $request->getHeaderLine('Authorization');
        $token = '';
        if (stripos($auth, 'Bearer ') === 0) {
            $token = trim(substr($auth, 7));
        }
        $expected = getenv('ANDROID_API_TOKEN') ?: env('ANDROID_API_TOKEN');
        if (!$expected || $token !== $expected) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['ok'=>false,'error'=>'unauthorized']);
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
