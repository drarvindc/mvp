<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Today — Visits</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .kv { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; font-size: 0.95rem; }
  .pill { margin: .25rem; }
</style>
</head>
<body class="p-3">
<div class="container" style="max-width: 980px;">
  <h3 class="mb-3">Today — Visits</h3>

  <form class="row g-3 align-items-end" onsubmit="return false;">
    <div class="col-sm-3">
      <label class="form-label">UID</label>
      <input class="form-control" id="uid" value="<?= esc($uid ?? '') ?>" placeholder="250001" required>
    </div>
    <div class="col-sm-3">
      <label class="form-label">Token</label>
      <input class="form-control" id="token" value="<?= htmlspecialchars($token ?? '') ?>">
    </div>
    <div class="col-sm-3 d-grid">
      <button class="btn btn-primary" id="btnGo">Show</button>
    </div>
    <div class="col-sm-3 d-grid">
      <a class="btn btn-outline-secondary" href="<?= site_url('admin/tools/visits-admin-view') ?>">Open Viewer</a>
    </div>
  </form>

  <hr>

  <div id="status" class="text-muted"></div>
  <div id="pills" class="my-3"></div>
</div>

<script>
const baseUrl = "<?= rtrim($baseUrl ?? '', '/') ?>";

function dmyToday(){
  const d = new Date();
  const dd = String(d.getDate()).padStart(2,'0');
  const mm = String(d.getMonth()+1).padStart(2,'0');
  const yy = d.getFullYear();
  return `${dd}-${mm}-${yy}`;
}

document.getElementById('btnGo').addEventListener('click', async () => {
  const uid = document.getElementById('uid').value.trim();
  const token = document.getElementById('token').value.trim();
  if (!uid) { alert('Enter UID'); return; }
  const date = dmyToday();

  const url = `${baseUrl}/api/visit/by-date?token=${encodeURIComponent(token)}&uid=${encodeURIComponent(uid)}&date=${encodeURIComponent(date)}&all=1`;
  document.getElementById('status').textContent = 'Loading...';
  const r = await fetch(url);
  const t = await r.text();
  let j;
  try { j = JSON.parse(t); } catch { j = { ok:false, error:'invalid_json', raw:t }; }
  document.getElementById('status').textContent = '';

  if (!j.ok) {
    document.getElementById('pills').innerHTML = `<div class="alert alert-danger">Failed: ${j.error||'unknown'}</div>`;
    return;
  }

  const pills = (j.results || []).map(v => {
    const count = (v.documents || []).length;
    const href = `<?= site_url('admin/tools/visits-admin-view') ?>?uid=${encodeURIComponent(uid)}&date=${encodeURIComponent(date)}`;
    return `<a class="btn btn-sm btn-outline-primary pill" href="${href}" title="Open viewer">${date} — Visit #${v.sequence} <span class="badge bg-secondary ms-1">${count}</span></a>`;
  }).join('');

  document.getElementById('pills').innerHTML = pills || `<div class="alert alert-warning">No visits yet for today.</div>`;
});
</script>
</body>
</html>
