<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DmyDateFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('datefmt');
        $get = $_GET;
        if (isset($get['date'])) {
            $iso = dmy_to_iso($get['date']);
            if ($iso) {
                $_GET['date'] = $iso;
                $_REQUEST['date'] = $iso;
            }
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
