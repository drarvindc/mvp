<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Database Migrations</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
</head>
<body class="hold-transition layout-top-nav">
<div class="container mt-5" style="max-width:880px">
  <div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Database Migrations</h3></div>
    <div class="card-body">
      <?php if(session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('message')) ?></div>
      <?php endif; ?>
      <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <?php $key = service('request')->getGet('key'); ?>

      <form method="post" action="<?= site_url('admin/tools/migrate/run') . '?key=' . urlencode($key) ?>" class="mb-2">
        <?= csrf_field() ?>
        <input type="hidden" name="key" value="<?= esc($key) ?>">
        <button type="submit" class="btn btn-primary"><i class="fas fa-play"></i> Run Migrations</button>
      </form>

      <form method="post" action="<?= site_url('admin/tools/migrate/rollback') . '?key=' . urlencode($key) ?>" class="mb-2">
        <?= csrf_field() ?>
        <input type="hidden" name="key" value="<?= esc($key) ?>">
        <button type="submit" class="btn btn-warning"><i class="fas fa-undo"></i> Rollback Last Batch</button>
      </form>

      <form method="post" action="<?= site_url('admin/tools/migrate/seed-species') . '?key=' . urlencode($key) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="key" value="<?= esc($key) ?>">
        <button type="submit" class="btn btn-success"><i class="fas fa-seedling"></i> Seed Species & Breeds</button>
      </form>

      <hr>
      <p class="text-muted">Protected by <code>adminauth</code>. Append <code>?key=YOUR_SECRET</code> to the URL.</p>
    </div>
  </div>
</div>
</body>
</html>
