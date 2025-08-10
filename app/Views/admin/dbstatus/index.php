<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DB Status</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition layout-top-nav">
<div class="container mt-5">
  <div class="card card-outline card-info">
    <div class="card-header">
      <h3 class="card-title">Database Status</h3>
    </div>
    <div class="card-body">
      <p><strong>Current Time:</strong> <?= esc($now) ?></p>
      <p><strong>Last Batch Number:</strong> <?= esc($lastBatch) ?></p>
      <p><strong>Total Applied Migrations:</strong> <?= esc($appliedCount) ?></p>
      <h5>Pending Migration Files:</h5>
      <?php if (empty($pendingFiles)): ?>
        <p class="text-success">No pending migrations. Database is up-to-date.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($pendingFiles as $file): ?>
            <li><?= esc($file) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
