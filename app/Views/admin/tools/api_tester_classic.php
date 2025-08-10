<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>API Tester — Classic</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin: 24px; }
    h2 { margin: 0 0 12px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; }
    label { display:block; font-weight:600; margin-top:8px; }
    input, select, button, textarea { width:100%; padding:8px; margin-top:4px; }
    button { cursor:pointer; }
    .muted{ color:#666; }
  </style>
</head>
<body>
  <h2>API Tester — Classic</h2>
  <div class="grid">
    <div class="card">
      <h3>Open Visit</h3>
      <form method="post" action="<?= site_url('api/visit/open') ?>" target="_blank">
        <label>UID</label>
        <input name="uid" placeholder="e.g., 250001" required>
        <label><input type="checkbox" name="forceNewVisit" value="1"> forceNewVisit</label>
        <button type="submit">POST /api/visit/open</button>
      </form>
    </div>

    <div class="card">
      <h3>Today</h3>
      <form method="get" action="<?= site_url('api/visit/today') ?>" target="_blank">
        <label>UID</label>
        <input name="uid" placeholder="e.g., 250001" required>
        <label>Date (optional)</label>
        <input name="date" type="date">
        <label><input type="checkbox" name="all" value="1" checked> all=1</label>
        <button type="submit">GET /api/visit/today</button>
      </form>
    </div>

    <div class="card" style="grid-column: span 2;">
      <h3>Upload Document</h3>
      <form method="post" action="<?= site_url('api/visit/upload') ?>" enctype="multipart/form-data" target="_blank">
        <div class="grid" style="grid-template-columns: 1fr 1fr 1fr 1fr;">
          <div>
            <label>UID</label>
            <input name="uid" placeholder="e.g., 250001" required>
          </div>
          <div>
            <label>Type (optional)</label>
            <input name="type" placeholder="rx / lab / img">
          </div>
          <div>
            <label>visitId (optional)</label>
            <input name="visitId" placeholder="leave blank to auto">
          </div>
          <div>
            <label>Backfill</label>
            <select name="backfill">
              <option value="1" selected>1 (default)</option>
              <option value="0">0</option>
            </select>
          </div>
        </div>
        <label>File</label>
        <input type="file" name="file" required>
        <button type="submit">POST /api/visit/upload</button>
        <div class="muted">Opens JSON in a new tab so you can save/copy response.</div>
      </form>
    </div>
  </div>
</body>
</html>
