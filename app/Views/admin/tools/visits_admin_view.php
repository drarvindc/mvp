<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Visits Admin View</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .kv { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; font-size: 0.95rem; }
  .doc-badge { font-size: .85rem; }
  .small-mono { font-size:.85rem; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
  pre.debug { background:#f8f9fa; border:1px solid #e9ecef; padding:.75rem; border-radius:.25rem; max-height:260px; overflow:auto; }
</style>
</head>
<body class="p-3">
<div class="container" style="max-width: 980px;">
  <h3 class="mb-3">Visits Admin View</h3>

  <form id="qform" class="row g-3 align-items-end">
    <div class="col-sm-3">
      <label class="form-label">UID</label>
      <input class="form-control" name="uid" id="uid" placeholder="250001" value="<?= esc($uid ?? '') ?>" required>
    </div>
    <div class="col-sm-3">
      <label class="form-label">Date (dd-mm-yyyy)</label>
      <input class="form-control" name="date" id="date" placeholder="11-08-2025" value="<?= esc($date ?? '') ?>" required>
    </div>
    <div class="col-sm-3">
      <label class="form-label">Token <span class="text-muted">(from .env or type)</span></label>
      <input class="form-control" name="token" id="token" value="<?= htmlspecialchars($token ?? '') ?>">
    </div>
    <div class="col-sm-3 d-grid">
      <button type="button" id="btnLoad" class="btn btn-primary">Load</button>
    </div>
    <div class="col-sm-3 d-grid">
      <button type="button" id="btnOpen" class="btn btn-outline-success">Open Today</button>
    </div>
  </form>

  <div class="row mt-3">
    <div class="col-md-7">
      <div id="status" class="mb-2 text-muted small-mono"></div>
      <div id="results"></div>
    </div>
    <div class="col-md-5">
      <div class="card">
        <div class="card-header py-2"><strong>Request Debug</strong></div>
        <div class="card-body">
          <div class="small-mono">Last URL:</div>
          <pre class="debug small-mono" id="dbgUrl">(none)</pre>
          <div class="small-mono">HTTP Status:</div>
          <pre class="debug small-mono" id="dbgStatus">(none)</pre>
          <div class="small-mono">Parsed JSON / Error:</div>
          <pre class="debug small-mono" id="dbgJson">(none)</pre>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const baseUrl = "<?= rtrim($baseUrl ?? '', '/') ?>";

function dmyToIso(dmy) {
  const m = dmy.match(/^(\\d{2})[-\\/](\\d{2})[-\\/](\\d{4})$/);
  if (!m) return dmy; // already ISO or empty
  return `${m[3]}-${m[2]}-${m[1]}`;
}

function esc(s){ return (s??'').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

function showDebug(url, status, obj) {
  document.getElementById('dbgUrl').textContent = url || '(none)';
  document.getElementById('dbgStatus').textContent = (status !== undefined) ? String(status) : '(none)';
  try {
    document.getElementById('dbgJson').textContent = (obj !== undefined)
      ? JSON.stringify(obj, null, 2)
      : '(none)';
  } catch(e) {
    document.getElementById('dbgJson').textContent = String(obj);
  }
}

async function loadByDate() {
  const uid = document.getElementById('uid').value.trim();
  const date = document.getElementById('date').value.trim();
  const token = document.getElementById('token').value.trim();

  if (!uid || !date) { alert('Enter UID and Date'); return; }

  const iso = dmyToIso(date);
  const url = `${baseUrl}/api/visit/by-date?token=${encodeURIComponent(token)}&uid=${encodeURIComponent(uid)}&date=${encodeURIComponent(iso)}`;

  document.getElementById('status').textContent = 'Loading visits...';
  showDebug(url, '(fetching...)', '(waiting)');

  let resp, status, body;
  try {
    resp = await fetch(url, { method: 'GET' });
    status = resp.status;
    body = await resp.text();
    let json;
    try { json = JSON.parse(body); } catch { json = { ok:false, error:'invalid_json', raw: body }; }

    showDebug(url, status, json);
    document.getElementById('status').textContent = '';

    renderResults(json, uid, iso);
  } catch (e) {
    showDebug(url, status, String(e));
    document.getElementById('status').textContent = 'Load failed';
    document.getElementById('results').innerHTML = `<div class="alert alert-danger">Network error</div>`;
  }
}

async function openToday() {
  const uid = document.getElementById('uid').value.trim();
  const token = document.getElementById('token').value.trim();
  if (!uid) { alert('Enter UID'); return; }

  const url = `${baseUrl}/api/visit/open?token=${encodeURIComponent(token)}`;
  const body = new URLSearchParams({ uid });

  document.getElementById('status').textContent = 'Opening/Fetching today\'s visit...';
  showDebug(url, '(fetching...)', '(waiting)');

  let resp, status, text;
  try {
    resp = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type':'application/x-www-form-urlencoded' },
      body
    });
    status = resp.status;
    text = await resp.text();
    let json;
    try { json = JSON.parse(text); } catch { json = { ok:false, error:'invalid_json', raw: text }; }

    showDebug(url, status, json);
    document.getElementById('status').textContent = json.ok ? 'Open OK' : `Open failed: ${esc(json.error || 'unknown')}`;
  } catch (e) {
    showDebug(url, status, String(e));
    document.getElementById('status').textContent = 'Open failed (network)';
  }
}

function renderResults(j, uid, iso) {
  const el = document.getElementById('results');
  if (!j || j.ok === false) {
    el.innerHTML = `<div class="alert alert-danger">Failed: ${esc(j && j.error || 'unknown')}</div>`;
    return;
  }
  const rows = (j.results || []).map(v => {
    const docs = (v.documents || []).map(d => {
      const url = `${baseUrl}/admin/visit/file?id=${encodeURIComponent(d.id)}`;
      return `<span class="badge text-bg-secondary doc-badge me-1">${esc(d.type||'-')} <a class="link-light" href="${url}" target="_blank">#${d.id}</a></span>`;
    }).join(' ');
    return `<div class="card mb-2">
      <div class="card-body">
        <div class="kv"><b>Visit</b> ${esc(v.id)} — ${esc(v.date)} seq ${esc(v.sequence)}</div>
        <div class="mt-2">${docs || '<span class="text-muted">No documents</span>'}</div>
      </div>
    </div>`;
  }).join('');
  el.innerHTML = rows || `<div class="alert alert-warning">No visits for ${esc(uid)} on ${esc(iso)}</div>`;
}

// Bind buttons
document.getElementById('btnLoad').addEventListener('click', loadByDate);
document.getElementById('btnOpen').addEventListener('click', openToday);

// If query string had uid/date, auto-fill happened in PHP; optionally auto-load
(function autoLoadIfReady(){
  const uid = document.getElementById('uid').value.trim();
  const date = document.getElementById('date').value.trim();
  if (uid && date) {
    // do not auto-fire; leave manual to avoid surprise calls
  }
})();
</script>
</body>
</html>
