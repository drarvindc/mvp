<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class Migrate extends BaseController
{
    public function index()
    {
        $migrate = \Config\Services::migrations();
        $ok = true;
        $msg = '';

        try {
            $migrate->latest();
            $msg = 'Migrations run successfully.';
        } catch (\Throwable $e) {
            $ok = false;
            $msg = 'Migration error: ' . $e->getMessage();
        }

        return $this->response->setContentType('text/html')->setBody(
            '<!doctype html><html><head><meta charset="utf-8"><title>Migrate</title>'.
            '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">'.
            '</head><body class="container py-4">'.
            '<h4>Run Migrations</h4>'.
            '<div class="alert '.($ok?'alert-success':'alert-danger').'">'.esc($msg).'</div>'.
            '<a class="btn btn-secondary" href="'.site_url('admin/tools').'">Back to Tools</a>'.
            '</body></html>'
        );
    }
}
