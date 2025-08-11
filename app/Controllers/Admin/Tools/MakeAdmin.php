<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class MakeAdmin extends BaseController
{
    public function index()
    {
        $key = $this->request->getGet('key');
        if ($key !== 'arvindrchauhan1723') {
            return $this->response->setStatusCode(401)->setBody('Unauthorized');
        }

        $email = trim((string)$this->request->getGet('email'));
        $password = (string)$this->request->getGet('password');
        $name = trim((string)$this->request->getGet('name')) ?: 'Admin';
        $role = trim((string)$this->request->getGet('role')) ?: 'admin';

        if (!$email || !$password) {
            return $this->response->setBody("Usage: ?key=arvindrchauhan1723&email=you@example.com&password=Secret123&name=Dr+Name&role=admin");
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $now = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();

        $exists = $db->table('users')->where('email', $email)->get()->getRowArray();
        if ($exists) {
            $db->table('users')->where('id', (int)$exists['id'])->update([
                'name' => $name,
                'password_hash' => $hash,
                'role' => $role,
                'updated_at' => $now,
            ]);
            $id = (int)$exists['id'];
            $action = 'updated';
        } else {
            $db->table('users')->insert([
                'name' => $name,
                'email' => $email,
                'password_hash' => $hash,
                'role' => $role,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $id = (int)$db->insertID();
            $action = 'created';
        }

        return $this->response->setJSON([
            'ok' => true,
            'action' => $action,
            'user_id' => $id,
            'email' => $email,
            'role' => $role,
        ]);
    }
}
