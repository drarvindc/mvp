<?php
namespace App\Controllers\Admin\Tools;

use App\Controllers\BaseController;

class Menu extends BaseController
{
    public function index()
    {
        $base = site_url('admin/tools');

        $html = <<<HTML
<!doctype html>
<html><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Tools</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-4">
<div class="container" style="max-width: 720px;">
  <h3 class="mb-3">Admin Tools</h3>
  <div class="list-group">
    <a class="list-group-item list-group-item-action" href="{$base}/migrate">Run Migration</a>
    <a class="list-group-item list-group-item-action" href="{$base}/api-tester">API Tester</a>
    <a class="list-group-item list-group-item-action" href="{$base}/api-tester-android">Android Upload Tester</a>
    <a class="list-group-item list-group-item-action" href="{$base}/api-tester-classic">Classic Upload Tester</a>
    <a class="list-group-item list-group-item-action" href="{$base}/stable-api-tester">Stable API Tester</a>
    <a class="list-group-item list-group-item-action" href="{$base}/db-check">DB Check</a>
  </div>
  <p class="text-muted mt-3">Dev bypass is <code>{(env('DEV_NO_AUTH', false) ? 'ON' : 'OFF')}</code></p>
</div>
</body></html>
HTML;

        return $this->response->setBody($html);
    }
}
