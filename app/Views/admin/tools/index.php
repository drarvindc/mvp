<?php
$urls = [
    'Migrations' => site_url('admin/tools/migrate'),
    'API Tester' => site_url('admin/tools/api-tester'),
    'Android Upload Tester' => site_url('admin/tools/api-tester-android'),
    'Classic Upload Tester' => site_url('admin/tools/api-tester-classic'),
    'DB Check' => site_url('admin/tools/db-check'),
];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Tools</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
  <h4 class="mb-3">Admin Tools</h4>
  <div class="list-group">
    <?php foreach ($urls as $label => $href): ?>
      <a class="list-group-item list-group-item-action" href="<?= $href ?>"><?= esc($label) ?></a>
    <?php endforeach; ?>
  </div>
</body>
</html>
