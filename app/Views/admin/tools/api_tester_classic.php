<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>API Tester (Classic)</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:16px;}
    .card{border:1px solid #e0e0e0;border-radius:8px;padding:12px;margin:12px 0;}
    label{font-weight:600;display:block;margin-top:8px;}
    input,select,button,textarea{width:100%;padding:8px;margin-top:6px;box-sizing:border-box;}
    button{cursor:pointer;}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    @media (max-width: 800px){.row{grid-template-columns:1fr;}}
    pre{background:#111;color:#0f0;padding:10px;border-radius:6px;overflow:auto;}
  </style>
</head>
<body>
  <h2>API Tester — Classic</h2>

  <div class="card">
    <h3>Open Visit</h3>
    <form method="post" action="<?= site_url('api/visit/open') ?>?token=<?= getenv('ANDROID_API_TOKEN') ?>">
      <label>UID (6 digits)</label>
      <input name="uid" placeholder="250001" required>
      <label style="display:flex;gap:6px;align-items:center;">
        <input type="checkbox" name="forceNewVisit" value="1"> forceNewVisit
      </label>
      <button type="submit">POST /api/visit/open</button>
    </form>
  </div>

  <div class="card">
    <h3>Upload Document</h3>
    <form method="post" enctype="multipart/form-data" action="<?= site_url('api/visit/upload') ?>?token=<?= getenv('ANDROID_API_TOKEN') ?>">
      <label>UID (6 digits)</label>
      <input name="uid" placeholder="250001" required>

      <label>Type</label>
      <select name="type" required>
        <option value="">-- select --</option>
        <option>rx</option>
        <option>photo</option>
        <option>doc</option>
        <option>xray</option>
        <option>lab</option>
        <option>usg</option>
        <option>invoice</option>
      </select>

      <label>Note (optional)</label>
      <input name="note" placeholder="e.g., front page">

      <label>File</label>
      <input type="file" name="file" required>

      <label style="display:flex;gap:6px;align-items:center;margin-top:10px;">
        <input type="checkbox" name="forceNewVisit" value="1"> forceNewVisit (create AM/PM visit)
      </label>

      <button type="submit">POST /api/visit/upload</button>
    </form>
  </div>

  <div class="card">
    <h3>Today</h3>
    <form method="get" action="<?= site_url('api/visit/today') ?>?token=<?= getenv('ANDROID_API_TOKEN') ?>">
      <label>UID</label>
      <input name="uid" placeholder="250001" required>
      <label>Date (optional)</label>
      <input name="date" type="date">
      <label style="display:flex;gap:6px;align-items:center;">
        <input type="checkbox" name="all" value="1" checked> all=1
      </label>
      <button type="submit">GET /api/visit/today</button>
    </form>
  </div>

  <div class="card">
    <h3>Manual Request</h3>
    <form id="manual">
      <label>Method</label>
      <select id="method"><option>GET</option><option>POST</option></select>
      <label>URL</label>
      <input id="url" value="<?= site_url('api/visit/today') ?>?uid=250001&all=1&token=<?= getenv('ANDROID_API_TOKEN') ?>">
      <label>Body (for POST)</label>
      <textarea id="body" placeholder="uid=250001&type=rx"></textarea>
      <button type="submit">Send</button>
    </form>
    <pre id="out">Ready.</pre>
  </div>

  <script>
  document.getElementById('manual').addEventListener('submit', async (e) => {
    e.preventDefault();
    const method = document.getElementById('method').value;
    const url = document.getElementById('url').value;
    const body = document.getElementById('body').value;
    const opt = { method };
    if (method === 'POST') {
      opt.headers = {'Content-Type': 'application/x-www-form-urlencoded'};
      opt.body = body;
    }
    const res = await fetch(url, opt);
    const txt = await res.text();
    document.getElementById('out').textContent = txt;
  });
  </script>
</body>
</html>
