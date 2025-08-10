<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Confirm Patient</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>.hit{outline:3px solid #0d6efd;}</style>
</head>
<body class="hold-transition layout-top-nav">
<div class="container mt-4" style="max-width:980px">
  <div class="card card-outline card-info">
    <div class="card-header"><h3 class="card-title">Review & Print</h3></div>
    <div class="card-body">
      <p><strong>Search:</strong> <?= esc($query) ?> (mode: <?= esc($mode) ?>)</p>

      <?php if($mode==='uid' && $petHit): ?>
        <div class="alert alert-success">Matched Unique ID <strong><?= esc($petHit['unique_id']) ?></strong>.</div>
      <?php endif; ?>

      <?php if (!empty($results)): ?>
        <div class="row">
          <?php foreach($results as $p): ?>
            <div class="col-md-6">
              <div class="card mb-3 <?= isset($petHit) && $petHit && $petHit['unique_id']===$p['unique_id'] ? 'hit' : '' ?>">
                <div class="card-body">
                  <h5 class="card-title"><?= esc($p['pet_name'] ?? 'Unnamed Pet') ?> <small class="text-muted">[<?= esc($p['unique_id']) ?>]</small></h5>
                  <p class="card-text mb-1"><?= esc($p['species'] ?? 'Species?') ?> • <?= esc($p['breed'] ?? 'Breed?') ?> • <?= esc($p['gender']) ?></p>
                  <a class="btn btn-primary" href="<?= site_url('patient/print-existing?uid=' . urlencode($p['unique_id'])) ?>">Print Letterhead</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">No pets found for this search.</div>
      <?php endif; ?>

      <hr>
      <h5>Create Provisional (New Patient)</h5>
      <form method="post" action="<?= site_url('patient/provisional/create') ?>">
        <?= csrf_field() ?>
        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label">Mobile Number</label>
            <input type="text" name="mobile" class="form-control" value="<?= esc($digits ?? $query) ?>" required>
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-outline-primary" type="submit">Generate Unique ID & Print</button>
          </div>
        </div>
      </form>

      <a class="btn btn-secondary mt-3" href="<?= site_url('patient/intake') ?>">Back</a>
    </div>
  </div>
</div>
</body>
</html>
