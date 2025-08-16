<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class StableApiAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (env('DEV_NO_AUTH', false)) {
            return;
        }

        $key = $request->getGet('key');
        if (!$key) {
            $key = $request->getHeaderLine('X-API-Key');
        }

        $allowed = array_filter([
            env('MIGRATE_WEB_KEY'),
            env('ANDROID_API_TOKEN'),
        ]);

        if ($key && in_array($key, $allowed, true)) {
            return;
        }

        $response = service('response');
        $response->setStatusCode(401);
        $response->setBody('Unauthorized (stable-api)');
        return $response;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
