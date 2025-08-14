<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminToolbar implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null) {}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (strpos($request->getUri()->getPath(), 'admin') !== 0) return;
        if (stripos($response->getHeaderLine('Content-Type'), 'text/html') === false) return;

        $session = session();
        if (!$session->get('admin_id')) return;

        $html = $response->getBody();
        $toolbar = '<div id="admintb" style="position:fixed;right:12px;bottom:12px;padding:8px 12px;background:#0d6efd;color:#fff;border-radius:6px;font:14px/1.2 system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, sans-serif;z-index:2147483647;box-shadow:0 6px 20px rgba(0,0,0,.2)">'
                 . '<a href="'.site_url('admin/tools').'" style="color:#fff;text-decoration:none;margin-right:10px">Tools</a>'
                 . '<a href="'.site_url('admin/logout').'" style="color:#fff;text-decoration:none">Logout</a>'
                 . '</div>';
        $html = str_ireplace('</body>', $toolbar . '</body>', $html, $count);
        if ($count === 0) $html .= $toolbar;
        $response->setBody($html);
    }
}
