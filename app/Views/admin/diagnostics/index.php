<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Diagnostics</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition layout-top-nav">
<div class="container mt-5">
  <div class="card card-outline card-secondary">
    <div class="card-header"><h3 class="card-title">Environment & DB</h3></div>
    <div class="card-body">
      <pre><?php echo esc(print_r($info, true)); ?></pre>
      <p class="mt-3 text-muted">
        Quick links:
        <a href="<?= site_url('admin/tools/db-status') ?>?key=<?= esc(service('request')->getGet('key')) ?>">DB Status</a> ·
        <a href="<?= base_url('version.json') ?>" target="_blank">version.json</a>
      </p>
    </div>
  </div>
</div>
</body>
</html>
