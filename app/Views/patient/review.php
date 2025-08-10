<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Confirm Patient</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition layout-top-nav">
<div class="container mt-5" style="max-width:800px">
  <div class="card card-outline card-info">
    <div class="card-header"><h3 class="card-title">Review & Print</h3></div>
    <div class="card-body">
      <p><strong>Query:</strong> <?= esc($query) ?></p>
      <div class="alert alert-warning">Results wiring to DB comes next. For now, use provisional print.</div>
      <a class="btn btn-primary" href="<?= site_url('patient/provisional') ?>">Print Provisional Letterhead</a>
      <a class="btn btn-secondary" href="<?= site_url('patient/intake') ?>">Back</a>
    </div>
  </div>
</div>
</body>
</html>
