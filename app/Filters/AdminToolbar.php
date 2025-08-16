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
        $path = ltrim($request->getUri()->getPath(), '/');
        if (strpos($path, 'admin') !== 0) return;
        if (!session()->get('admin_id')) return;

        $ctype = $response->getHeaderLine('Content-Type');
        $body  = (string) $response->getBody();
        $looksHtml = (stripos($ctype, 'text/html') !== false) || (stripos($body, '<html') !== false);
        if (!$looksHtml) return;

        $toolbar = '<div id="admintb" style="position:fixed;right:12px;bottom:12px;padding:8px 12px;background:#0d6efd;color:#fff;border-radius:6px;font:14px/1.2 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif;z-index:2147483647;box-shadow:0 6px 20px rgba(0,0,0,.2)">'
                 . '<a href="'.site_url('admin/tools').'" style="color:#fff;text-decoration:none;margin-right:12px">Tools</a>'
                 . '<a href="'.site_url('admin/logout').'" style="color:#fff;text-decoration:none">Logout</a>'
                 . '</div>';

        if (stripos($body, '</body>') !== false) {
            $body = str_ireplace('</body>', $toolbar . '</body>', $body);
        } else {
            $body .= $toolbar;
        }
        $response->setBody($body);
    }
}
