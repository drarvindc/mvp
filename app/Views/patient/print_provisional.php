<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Clinic Letterhead</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>
    @media print { .no-print { display:none; } }
    .letter { padding: 24px; }
    .codes { display:flex; gap:20px; align-items:center; margin:8px 0 16px; }
    .codes img { border:1px solid #ddd; padding:4px; background:#fff; }
  </style>
</head>
<body>
<div class="letter">
  <h3>Clinic Letterhead</h3>
  <p>Date: <?= date('Y-m-d') ?></p>
  <p>Unique ID: <strong><?= esc($uid ?? 'TBD') ?></strong></p>
  <div class="codes">
    <img src="<?= site_url('media/barcode-uid?uid=' . urlencode($uid ?? '000000')) ?>" alt="Barcode" height="60">
    <img src="<?= site_url('media/qr-uid?uid=' . urlencode($uid ?? '')) ?>" alt="QR" width="120" height="120">
  </div>
  <?php if(!empty($pet)): ?>
    <p>Pet: <?= esc($pet['pet_name'] ?? '') ?> (Owner: <?= esc(($pet['first_name'] ?? '') . ' ' . ($pet['last_name'] ?? '')) ?>)</p>
  <?php endif; ?>
  <?php if(!empty($mobile)): ?><p>Mobile: <?= esc($mobile) ?></p><?php endif; ?>
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
