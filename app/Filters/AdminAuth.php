<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $valid = getenv('MIGRATE_WEB_KEY');
        $valid = $valid !== false ? trim((string)$valid) : '';

        $key = trim((string)($request->getGet('key') ?? ''));
        if ($key === '') $key = trim((string)($request->getPost('key') ?? ''));
        if ($key === '') $key = trim((string)$request->getHeaderLine('X-Migrate-Key'));

        if ($valid !== '' && $key !== '' && hash_equals($valid, $key)) {
            return;
        }

        return redirect()->to('/');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
