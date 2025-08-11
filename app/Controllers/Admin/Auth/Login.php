<?php
namespace App\Controllers\Admin\Auth;

use App\Controllers\BaseController;

class Login extends BaseController
{
    public function index()
    {
        $next = $this->request->getGet('next') ?: site_url('admin');
        return view('admin/auth/login', ['next' => $next, 'error' => null]);
    }

    public function attempt()
    {
        $email = trim((string)$this->request->getPost('email'));
        $password = (string)$this->request->getPost('password');
        $remember = $this->request->getPost('remember') ? true : false;
        $next = $this->request->getPost('next') ?: site_url('admin');

        if ($email === '' || $password === '') {
            return view('admin/auth/login', ['next'=>$next, 'error'=>'Email and password are required.']);
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('email', $email)->get()->getRowArray();
        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            return view('admin/auth/login', ['next'=>$next, 'error'=>'Invalid credentials.']);
        }

        $session = session();
        $session->set([
            'admin_id' => (int)$user['id'],
            'admin_email' => $user['email'],
            'admin_name' => $user['name'],
            'admin_role' => $user['role'],
            'is_admin_logged_in' => true,
        ]);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            try {
                $db->table('api_tokens')->insert([
                    'user_id' => (int)$user['id'],
                    'token' => $token,
                    'last_used_at' => date('Y-m-d H:i:s'),
                    'revoked' => 0,
                ]);
            } catch (\Throwable $e) { /* ignore */ }

            $response = service('response');
            $response->setCookie('admin_remember', $token, 60*60*24*30, '', '', true, true, null, 'Lax');
        }

        return redirect()->to($next);
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        $resp = service('response');
        $resp->deleteCookie('admin_remember');
        return redirect()->to(site_url('admin/login'));
    }

    public function logoutAll()
    {
        $session = session();
        $userId = (int)($session->get('admin_id') ?: 0);
        if ($userId) {
            $db = \Config\Database::connect();
            $db->table('api_tokens')->where('user_id', $userId)->update(['revoked' => 1]);
        }
        $session->destroy();
        $resp = service('response');
        $resp->deleteCookie('admin_remember');
        return redirect()->to(site_url('admin/login'))->with('message', 'Logged out everywhere.');
    }
}
