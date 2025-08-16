<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class DbCheck extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $out = ['indexes'=>[], 'columns'=>[], 'counts'=>[]];

        try {
            $idx = $db->query("SHOW INDEX FROM visits WHERE Key_name='idx_visits_pet_date_seq'")->getResultArray();
            $out['indexes']['visits_idx'] = $idx ? 'OK (exists)' : 'MISSING';
        } catch (\Throwable $e) { $out['indexes']['visits_idx'] = 'ERROR: ' . $e->getMessage(); }

        foreach (['pet_id','mime','note','visit_id','filename'] as $col) {
            try { $row = $db->query("SHOW COLUMNS FROM documents WHERE Field=?", [$col])->getRowArray();
                  $out['columns'][$col] = $row ? 'OK' : 'MISSING';
            } catch (\Throwable $e) { $out['columns'][$col] = 'ERROR: ' . $e->getMessage(); }
        }

        foreach (['pets','visits','documents'] as $t) {
            try { $c = $db->table($t)->countAllResults(); $out['counts'][$t] = $c; }
            } catch (\Throwable $e) { $out['counts'][$t] = 'ERROR: ' . $e->getMessage(); }
        }

        ob_start(); ?>
        <!doctype html>
        <html><head>
        <meta charset="utf-8"><title>DB Check</title>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        </head><body class="container py-4">
        <h4 class="mb-3">DB Check</h4>

        <h6 class="mt-3">Indexes</h6>
        <table class="table table-sm">
          <thead><tr><th>Item</th><th>Status</th></tr></thead>
          <tbody><tr><td>visits: idx_visits_pet_date_seq</td><td><?= esc($out['indexes']['visits_idx']) ?></td></tr></tbody>
        </table>

        <h6 class="mt-3">Documents Columns</h6>
        <table class="table table-sm">
          <thead><tr><th>Column</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($out['columns'] as $k=>$v): ?><tr><td><?= esc($k) ?></td><td><?= esc($v) ?></td></tr><?php endforeach; ?>
          </tbody>
        </table>

        <h6 class="mt-3">Row Counts</h6>
        <table class="table table-sm">
          <thead><tr><th>Table</th><th>Count</th></tr></thead>
          <tbody>
          <?php foreach ($out['counts'] as $k=>$v): ?><tr><td><?= esc($k) ?></td><td><?= esc(strval($v)) ?></td></tr><?php endforeach; ?>
          </tbody>
        </table>

        <a class="btn btn-link mt-3" href="<?= site_url('admin/tools') ?>">Back to Tools</a>
        </body></html>
        <?php
        return $this->response->setContentType('text/html')->setBody(ob_get_clean());
    }
}
