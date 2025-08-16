<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class Migrate extends BaseController
{
    public function index()
    {
        helper(['form']);
        $action = $this->request->getPost('action');
        $ok = null; $msg = '';

        if ($action) {
            try {
                $migrate = \Config\Services::migrations();
                switch ($action) {
                    case 'latest':  $migrate->latest(); $ok = true;  $msg = 'Migrations run to latest successfully.'; break;
                    case 'rollback': $migrate->regress(-1); $ok = true;  $msg = 'Rolled back last batch successfully.'; break;
                    case 'seed':
                        $seeder = \Config\Database::seeder();
                        $class = $this->request->getPost('seeder') ?: 'DatabaseSeeder';
                        $seeder->call($class);
                        $ok = true;  $msg = 'Seeder "' . esc($class) . '" executed successfully.'; break;
                    default: $ok = false; $msg = 'Unknown action.';
                }
            } catch (\Throwable $e) { $ok = false; $msg = 'Error: ' . $e->getMessage(); }
        }

        ob_start(); ?>
        <!doctype html>
        <html><head>
        <meta charset="utf-8"><title>Migrations</title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        </head><body class="container py-4">
        <h4 class="mb-3">Migrations</h4>

        <?php if ($ok !== null): ?>
            <div class="alert <?= $ok ? 'alert-success' : 'alert-danger' ?>"><?= esc($msg) ?></div>
        <?php endif; ?>

        <form method="post" class="mb-3">
          <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
          <div class="btn-group" role="group">
            <button name="action" value="latest" class="btn btn-primary">Run Latest</button>
            <button name="action" value="rollback" class="btn btn-warning">Rollback Last Batch</button>
          </div>
        </form>

        <form method="post" class="row gy-2 gx-2 align-items-center">
          <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
          <div class="col-auto"><input type="text" name="seeder" class="form-control" placeholder="Seeder class (e.g. DatabaseSeeder)"></div>
          <div class="col-auto"><button name="action" value="seed" class="btn btn-outline-secondary">Run Seeder</button></div>
        </form>

        <a class="btn btn-link mt-3" href="<?= site_url('admin/tools') ?>">Back to Tools</a>
        </body></html>
        <?php
        return $this->response->setContentType('text/html')->setBody(ob_get_clean());
    }
}
