<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>API Tester</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin: 16px; }
    textarea, input, select, button { width: 100%; padding: 8px; margin: 6px 0; }
    pre { background: #111; color: #0f0; padding: 10px; border-radius: 6px; overflow: auto; }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; margin: 8px 0; }
  </style>
</head>
<body>
  <h2>API Tester</h2>

  <div class="card">
    <h3>Quick actions</h3>
    <div class="row">
      <div>
        <label>Open Visit (uid + optional forceNewVisit)</label>
        <form method="post" action="<?= site_url('api/visit/open') ?>">
          <input name="uid" placeholder="UID e.g. 250001" required>
          <label style="display:flex;align-items:center;gap:6px;">
            <input type="checkbox" name="forceNewVisit" value="1"> forceNewVisit
          </label>
          <button type="submit">POST /api/visit/open</button>
        </form>
      </div>
      <div>
        <label>Today (uid + optional date)</label>
        <form method="get" action="<?= site_url('api/visit/today') ?>">
          <input name="uid" placeholder="UID e.g. 250001" required>
          <input name="date" type="date">
          <label style="display:flex;align-items:center;gap:6px;">
            <input type="checkbox" name="all" value="1" checked> all=1
          </label>
          <button type="submit">GET /api/visit/today</button>
        </form>
      </div>
    </div>
  </div>

  <div class="card">
    <h3>Manual request</h3>
    <form id="manual">
      <select id="method">
        <option>GET</option>
        <option>POST</option>
      </select>
      <input id="url" placeholder="/index.php/api/visit/today?uid=250001&all=1" value="<?= site_url('api/visit/today') ?>?uid=250001&all=1">
      <textarea id="body" placeholder="name=value&other=123 (for POST)"></textarea>
      <button type="submit">Send</button>
    </form>
    <pre id="out">Ready.</pre>
  </div>

  <script>
  const manual = document.getElementById('manual');
  manual.addEventListener('submit', async (e) => {
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
    const text = await res.text();
    document.getElementById('out').textContent = text;
  });
  </script>
</body>
</html>
