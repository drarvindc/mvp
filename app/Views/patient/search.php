<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Patient Intake</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <script>
    window.onload = () => { const i = document.getElementById('q'); if(i){ i.focus(); } };
  </script>
</head>
<body class="hold-transition layout-top-nav">
<div class="container mt-5" style="max-width:640px">
  <div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Enter Mobile or Unique ID</h3></div>
    <div class="card-body">
      <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>
      <form method="post" action="<?= site_url('patient/find') ?>">
        <?= csrf_field() ?>
        <input type="text" name="q" id="q" class="form-control form-control-lg" placeholder="Scan or type here…" autocomplete="off">
        <button class="btn btn-primary mt-3" type="submit">Search</button>
        <a class="btn btn-outline-secondary mt-3" href="<?= site_url('patient/intake') ?>">Clear</a>
      </form>
      <p class="text-muted mt-2">Tip: 6 digits = Unique ID, anything else treated as mobile.</p>
    </div>
  </div>
</div>
</body>
</html>
