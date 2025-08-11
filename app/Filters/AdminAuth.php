<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $uri = trim($request->uri->getPath(), '/');
        // Allow login pages and make-admin bootstrap without session
        if ($this->isWhitelisted($uri)) {
            return;
        }

        $session = session();
        if ($session->get('admin_id')) {
            return; // already logged in
        }

        // Try remember-me cookie
        $cookie = service('request')->getCookie('admin_remember');
        if (!$cookie && isset($_COOKIE['admin_remember'])) {
            $cookie = $_COOKIE['admin_remember'];
        }
        if ($cookie) {
            $db = \Config\Database::connect();
            $row = $db->table('api_tokens')->where([
                'token' => $cookie,
                'revoked' => 0
            ])->get()->getRowArray();
            if ($row) {
                $user = $db->table('users')->where('id', (int)$row['user_id'])->get()->getRowArray();
                if ($user) {
                    $session->set([
                        'admin_id' => (int)$user['id'],
                        'admin_email' => $user['email'],
                        'admin_name' => $user['name'],
                        'admin_role' => $user['role'],
                        'is_admin_logged_in' => true,
                    ]);
                    try {
                        $db->table('api_tokens')->where('id', (int)$row['id'])->update([
                            'last_used_at' => date('Y-m-d H:i:s')
                        ]);
                    } catch (\Throwable $e) {}
                    return;
                }
            }
        }

        $loginUrl = site_url('admin/login') . '?next=' . rawurlencode('/' . $uri);
        return redirect()->to($loginUrl);
    }

    private function isWhitelisted(string $uri): bool
    {
        $uri = preg_replace('#^index\.php/#', '', $uri);
        $whitelist = [
            'admin/login',
            'admin/auth/login',
            'admin/tools/make-admin',
        ];
        foreach ($whitelist as $w) {
            if (stripos($uri, $w) === 0) return true;
        }
        return false;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
