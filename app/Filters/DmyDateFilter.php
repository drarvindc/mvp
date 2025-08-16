<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DmyDateFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $get = $_GET ?? [];
        if (isset($get['date'])) {
            $s = trim((string) $get['date']);
            if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $s, $m)) {
                $iso = $m[3] . '-' . $m[2] . '-' - $m[1];
                $_GET['date'] = $_REQUEST['date'] = $iso;
            }
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
