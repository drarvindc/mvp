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
  .visit-col { min-width: 300px; }
</style>
</head>
<body class="p-3">
<div class="container" style="max-width: 1100px;">
  <h3 class="mb-3">Visits Admin View</h3>

  <form id="qform" class="row g-3 align-items-end">
    <div class="col-sm-3">
      <label class="form-label">UID</label>
      <input class="form-control" name="uid" id="uid" placeholder="250001" value="<?= esc($uid ?? '') ?>" required>
    </div>
    <div class="col-sm-3">
      <label class="form-label">Date</label>
      <input type="date" class="form-control" name="date" id="date" value="">
      <div class="form-text">You can also paste dd-mm-yyyy</div>
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

    <div class="col-sm-9">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="chkAll">
        <label class="form-check-label" for="chkAll">Show all visits for the day (Visit #1 / #2 ...)</label>
      </div>
    </div>
  </form>

  <div class="mt-3">
    <div class="btn-group btn-group-sm" role="group" aria-label="Type filters">
      <button type="button" class="btn btn-outline-secondary type-filter active" data-type="">All</button>
      <button type="button" class="btn btn-outline-secondary type-filter" data-type="rx">rx</button>
      <button type="button" class="btn btn-outline-secondary type-filter" data-type="photo">photo</button>
      <button type="button" class="btn btn-outline-secondary type-filter" data-type="doc">doc</button>
      <button type="button" class="btn btn-outline-secondary type-filter" data-type="xray">xray</button>
      <button type="button" class="btn btn-outline-secondary type-filter" data-type="lab">lab</button>
      <button type="button" class="btn btn-outline-secondary type-filter" data-type="usg">usg</button>
      <button type="button" class="btn btn-outline-secondary type-filter" data-type="invoice">invoice</button>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-lg-7">
      <div id="status" class="mb-2 text-muted small-mono"></div>
      <div id="results"></div>
    </div>
    <div class="col-lg-5">
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

function isIsoDate(s){ return /^\d{4}-\d{2}-\d{2}$/.test(s); }
function isDmyDate(s){ return /^\d{2}-\d{2}-\d{4}$/.test(s); }
function dmyToIso(dmy) {
  const m = dmy.match(/^(\d{2})[-/](\d{2})[-/](\d{4})$/);
  if (!m) return dmy;
  return `${m[3]}-${m[2]}-${m[1]}`;
}
function isoToDmy(iso) {
  const m = iso.match(/^(\d{4})-(\d{2})-(\d{2})$/);
  if (!m) return iso;
  return `${m[3]}-${m[2]}-${m[1]}`;
}
(function initDateFromQuery(){
  const q = "<?= esc($date ?? '') ?>";
  const input = document.getElementById('date');
  if (!q) return;
  if (isDmyDate(q))      input.value = dmyToIso(q);
  else if (isIsoDate(q)) input.value = q;
})();

function esc(s){ return (s??'').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }
function showDebug(url, status, obj) {
  document.getElementById('dbgUrl').textContent = url || '(none)';
  document.getElementById('dbgStatus').textContent = (status !== undefined) ? String(status) : '(none)';
  try { document.getElementById('dbgJson').textContent = (obj !== undefined) ? JSON.stringify(obj, null, 2) : '(none)'; }
  catch(e) { document.getElementById('dbgJson').textContent = String(obj); }
}

let currentTypeFilter = '';
document.querySelectorAll('.type-filter').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.type-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentTypeFilter = btn.dataset.type || '';
    // re-render using cached last JSON (if any)
    const raw = document.getElementById('dbgJson').textContent;
    try {
      const j = JSON.parse(raw);
      const uid = document.getElementById('uid').value.trim();
      let d = document.getElementById('date').value.trim();
      d = isIsoDate(d) ? isoToDmy(d) : d;
      renderResults(j, uid, d);
    } catch {}
  });
});

async function loadByDate() {
  const uid = document.getElementById('uid').value.trim();
  let dateVal = document.getElementById('date').value.trim();
  const token = document.getElementById('token').value.trim();
  const all = document.getElementById('chkAll').checked ? 1 : 0;

  if(!uid || !dateVal) { alert('Enter UID and Date'); return; }

  if (isIsoDate(dateVal)) dateVal = isoToDmy(dateVal);

  const url = `${baseUrl}/api/visit/by-date?token=${encodeURIComponent(token)}&uid=${encodeURIComponent(uid)}&date=${encodeURIComponent(dateVal)}&all=${all}`;
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
    renderResults(json, uid, dateVal);
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

    if (json && json.ok && json.visit && json.visit.date) {
      const dmy = json.visit.date;
      const iso = dmyToIso(dmy);
      const dateInput = document.getElementById('date');
      dateInput.value = isIsoDate(iso) ? iso : dateInput.value;
      document.getElementById('status').textContent = 'Open OK → Loading list...';
      await loadByDate();
    } else {
      document.getElementById('status').textContent = `Open finished: ${esc(json && json.error || 'unknown')}`;
    }
  } catch (e) {
    showDebug(url, status, String(e));
    document.getElementById('status').textContent = 'Open failed (network)';
  }
}

function renderResults(j, uid, dmy) {
  const el = document.getElementById('results');
  if (!j || j.ok === false) {
    el.innerHTML = `<div class="alert alert-danger">Failed: ${esc(j && j.error || 'unknown')}</div>`;
    return;
  }

  const results = Array.isArray(j.results) ? j.results : [];
  if (results.length === 0) {
    el.innerHTML = `<div class="alert alert-warning">No visits for ${esc(uid)} on ${esc(dmy)}</div>`;
    return;
  }

  if (document.getElementById('chkAll').checked && results.length > 1) {
    // side-by-side columns
    const cols = results.map(v => visitCard(v)).join('');
    el.innerHTML = `<div class="row g-3">${cols}</div>`;
  } else {
    // single list
    el.innerHTML = results.map(v => visitCard(v)).join('');
  }

  function visitCard(v) {
    const docs = (v.documents || []).filter(d => {
      if (!currentTypeFilter) return true;
      return (d.type || '').toLowerCase() === currentTypeFilter;
    }).map(d => {
      const url = d.url || `${baseUrl}/admin/visit/file?id=${encodeURIComponent(d.id)}`;
      const type = (d.type || '-').toLowerCase();
      return `<span class="badge text-bg-secondary doc-badge me-1">${esc(type)} <a class="link-light" href="${url}" target="_blank">#${d.id}</a></span>`;
    }).join(' ');

    return `<div class="col visit-col">
      <div class="card mb-2 h-100">
        <div class="card-body">
          <div class="kv"><b>Visit</b> ${esc(v.id)} — ${esc(v.date)} seq ${esc(v.sequence)}</div>
          <div class="mt-2">${docs || '<span class="text-muted">No documents</span>'}</div>
        </div>
      </div>
    </div>`;
  }
}

// Bind
document.getElementById('btnLoad').addEventListener('click', loadByDate);
document.getElementById('btnOpen').addEventListener('click', openToday);
</script>
</body>
</html>
