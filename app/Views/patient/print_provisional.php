<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Provisional Letterhead</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>
    @media print { .no-print { display:none; } }
    .letter { padding: 24px; }
  </style>
</head>
<body>
<div class="letter">
  <h3>Clinic Letterhead</h3>
  <p>Date: <?= date('Y-m-d') ?></p>
  <p>Unique ID: <?= esc($uid ?? 'TBD') ?></p>
  <p>Mobile: <?= esc($mobile ?? 'TBD') ?></p>
  <hr>
  <p>Pet Name: _____________________________</p>
  <p>Owner Name: ___________________________</p>
  <p>Age: __________ Gender: __________ Species: __________</p>
  <p>Notes:</p>
  <div style="height:160px;border:1px dashed #aaa;"></div>
  <hr>
  <button class="btn btn-primary no-print" onclick="window.print()">Print</button>
  <a class="btn btn-secondary no-print" href="<?= site_url('patient/intake') ?>">Back</a>
</div>
</body>
</html>
