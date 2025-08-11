<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Stable API Tester</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-3">
  <h4>Stable API Tester</h4>
  <div class="alert alert-info">Endpoints are under <code>/index.php/stable-api/...</code>. Token from <code>.env ANDROID_API_TOKEN</code>. You can also pass <code>?token=</code>.</div>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Open Visit</h5>
          <form method="post" action="<?= site_url('stable-api/visit/open') ?>?token=<?= urlencode($token) ?>">
            <div class="mb-2">
              <label class="form-label">UID</label>
              <input name="uid" class="form-control" placeholder="250001" required>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="forceNewVisit" value="1" id="force">
              <label class="form-check-label" for="force">forceNewVisit</label>
            </div>
            <button class="btn btn-primary">POST /stable-api/visit/open</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Today / By Date</h5>
          <form method="get" action="<?= site_url('stable-api/visit/today') ?>">
            <div class="mb-2">
              <label class="form-label">UID</label>
              <input name="uid" class="form-control" placeholder="250001" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Date</label>
              <input name="date" type="date" class="form-control">
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="all" value="1" id="all" checked>
              <label class="form-check-label" for="all">all=1</label>
            </div>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
            <button class="btn btn-secondary">GET /stable-api/visit/today</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <h5 class="card-title">Upload Document</h5>
      <form method="post" enctype="multipart/form-data" action="<?= site_url('stable-api/visit/upload') ?>?token=<?= urlencode($token) ?>">
        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label">UID</label>
            <input name="uid" class="form-control" placeholder="250001" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select" required>
              <option value="">--select--</option>
              <option>rx</option><option>photo</option><option>doc</option><option>xray</option><option>lab</option><option>usg</option><option>invoice</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Note (optional)</label>
            <input name="note" class="form-control" placeholder="front/back etc.">
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="forceNewVisit" value="1" id="force2">
              <label class="form-check-label" for="force2">forceNewVisit</label>
            </div>
          </div>
        </div>
        <div class="mt-2">
          <input type="file" name="file" class="form-control" required>
        </div>
        <button class="btn btn-success mt-2">POST /stable-api/visit/upload</button>
      </form>
    </div>
  </div>
</body>
</html>
