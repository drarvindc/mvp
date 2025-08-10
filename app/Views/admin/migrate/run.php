<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Migrations Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
  <style>
    .wrap{max-width:880px;margin:40px auto}
    .card{border-radius:12px}
    .btn{border-radius:10px}
  </style>
</head>
<body class="hold-transition layout-top-nav">
  <div class="wrap">
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title">Database Migrations</h3>
      </div>
      <div class="card-body">
        <?php if(session()->getFlashdata('message')): ?>
          <div class="alert alert-success"><?= esc(session()->getFlashdata('message')) ?></div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
          <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <p class="text-muted mb-3">Now: <strong><?= esc($now) ?></strong> (Asia/Kolkata)</p>

        <form method="post" action="<?= site_url('admin/tools/migrate/run') ?>" class="mb-2">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-primary"><i class="fas fa-play"></i> Run Migrations</button>
        </form>

        <form method="post" action="<?= site_url('admin/tools/migrate/rollback') ?>" class="mb-2">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-warning"><i class="fas fa-undo"></i> Rollback Last Batch</button>
        </form>

        <form method="post" action="<?= site_url('admin/tools/migrate/seed-species') ?>">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-success"><i class="fas fa-seedling"></i> Seed Species & Breeds</button>
        </form>

        <hr>
        <p class="text-muted">Protect this page: require admin login or append <code>?key=YOUR_SECRET</code>. Set <code>MIGRATE_WEB_KEY</code> in your <code>.env</code>.</p>
      </div>
    </div>
  </div>
</body>
</html>
