<?php
$base = site_url('admin/tools');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tools</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
  <h4 class="mb-3">Admin Tools</h4>
  <div class="list-group">
    <a class="list-group-item list-group-item-action" href="<?= site_url('admin/tools/migrate') ?>">Run Migrations</a>
    <a class="list-group-item list-group-item-action" href="<?= site_url('admin/tools/api-tester') ?>">API Tester</a>
    <a class="list-group-item list-group-item-action" href="<?= site_url('admin/tools/api-tester-android') ?>">Android Upload Tester</a>
    <a class="list-group-item list-group-item-action" href="<?= site_url('admin/tools/api-tester-classic') ?>">Classic Upload Tester</a>
    <a class="list-group-item list-group-item-action" href="<?= site_url('admin/tools/db-check') ?>">DB Check</a>
  </div>
</body>
</html>
