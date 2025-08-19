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
</style>
</head>
<body class="p-3">
<div class="container" style="max-width: 960px;">
  <h3 class="mb-3">Visits Admin View</h3>

  <form id="qform" class="row g-3 align-items-end">
    <div class="col-sm-3">
      <label class="form-label">UID</label>
      <input class="form-control" name="uid" id="uid" placeholder="250001" required>
    </div>
    <div class="col-sm-3">
      <label class="form-label">Date (dd-mm-yyyy)</label>
      <input class="form-control" name="date" id="date" placeholder="11-08-2025" required>
    </div>
    <div class="col-sm-3">
      <label class="form-label">Token <span class="text-muted">(from .env or type)</span></label>
      <input class="form-control" name="token" id="token" value="<?= htmlspecialchars($token ?? '') ?>">
    </div>
    <div class="col-sm-3">
      <button type="button" id="btnLoad" class="btn btn-primary w-100">Load</button>
    </div>
    <div class="col-sm-3">
      <button type="button" id="btnOpen" class="btn btn-outline-success w-100">Open Today</button>
    </div>
  </form>

  <hr class="my-4">

  <div id="status" class="mb-3 text-muted"></div>
  <div id="results"></div>
</div>

<script>
const baseUrl = "<?= $baseUrl ?>";

function dmyToIso(dmy) {
  const m = dmy.match(/^(\\d{2})[-\\/](\\d{2})[-\\/](\\d{4})$/);
  if (!m) return dmy; // pass-through if already ISO
  return `${m[3]}-${m[2]}-${m[1]}`;
}

function esc(s){ return (s??'').toString().replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

async function loadByDate() {
  const uid = document.getElementById('uid').value.trim();
  const date = document.getElementById('date').value.trim();
  const token = document.getElementById('token').value.trim();
  if(!uid || !date) { alert('Enter UID and Date'); return; }
  const iso = dmyToIso(date);
  const url = `${baseUrl}/api/visit/by-date?token=${encodeURIComponent(token)}&uid=${encodeURIComponent(uid)}&date=${encodeURIComponent(iso)}`;
  document.getElementById('status').textContent = 'Loading...';
  const r = await fetch(url);
  const j = await r.json().catch(()=>({ok:false,error:'invalid_json'}));
  document.getElementById('status').textContent = '';
  renderResults(j, uid, iso);
}

async function openToday() {
  const uid = document.getElementById('uid').value.trim();
  const token = document.getElementById('token').value.trim();
  if(!uid){ alert('Enter UID'); return; }
  const url = `${baseUrl}/api/visit/open?token=${encodeURIComponent(token)}`;
  const body = new URLSearchParams({ uid });
  document.getElementById('status').textContent = 'Opening/Fetching today\\'s visit...';
  const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body });
  const j = await r.json().catch(()=>({ok:false,error:'invalid_json'}));
  document.getElementById('status').textContent = j.ok ? 'Open OK' : `Open failed: ${esc(j && j.error || 'unknown')}`;
}

function renderResults(j, uid, iso) {
  const el = document.getElementById('results');
  if(!j || j.ok === false) {
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

document.getElementById('btnLoad').addEventListener('click', loadByDate);
document.getElementById('btnOpen').addEventListener('click', openToday);
</script>
</body>
</html>
