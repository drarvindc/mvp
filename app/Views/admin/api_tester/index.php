<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>API Tester</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<style>pre{background:#f7f7f7;padding:10px;border-radius:6px;max-height:300px;overflow:auto}</style>
</head>
<body class="hold-transition layout-top-nav">
<div class="container mt-4">
  <div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title">Android API – Point & Click Tester</h3>
      <span class="small text-muted">JS fetch with Bearer token</span>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Base URL</label>
            <input id="baseUrl" class="form-control" value="<?= site_url() ?>">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>API Token (ANDROID_API_TOKEN)</label>
            <input id="apiToken" class="form-control" placeholder="Paste token from .env">
          </div>
        </div>
      </div>

      <hr>

      <h5>1) Open / Ensure Today’s Visit</h5>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label>UID (6 digits)</label>
            <input id="uidOpen" class="form-control" placeholder="250001">
          </div>
        </div>
        <div class="col-md-2 align-self-end">
          <button class="btn btn-primary" onclick="openVisit()">Open Visit</button>
        </div>
      </div>
      <pre id="outOpen"></pre>

      <hr>

      <h5>2) Upload Attachment</h5>
      <div class="row">
        <div class="col-md-3">
          <div class="form-group"><label>UID</label>
            <input id="uidUpload" class="form-control" placeholder="250001">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group"><label>Type</label>
            <select id="typeUpload" class="form-control">
              <option>rx</option><option>photo</option><option>doc</option>
              <option>xray</option><option>lab</option><option>usg</option><option>invoice</option>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group"><label>Note</label>
            <input id="noteUpload" class="form-control" placeholder="front">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group"><label>Force New Visit</label><br>
            <input type="checkbox" id="forceUpload"> Create new visit even if one exists today
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group"><label>Select file</label>
            <input id="fileUpload" type="file" class="form-control">
          </div>
        </div>
        <div class="col-md-2 align-self-end">
          <button class="btn btn-success" onclick="uploadFile()">Upload</button>
        </div>
      </div>
      <pre id="outUpload"></pre>

      <hr>

      <h5>3) Get Today’s Visit</h5>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group"><label>UID</label>
            <input id="uidToday" class="form-control" placeholder="250001">
          </div>
        </div>
        <div class="col-md-2 align-self-end">
          <button class="btn btn-secondary" onclick="getToday()">Fetch</button>
        </div>
      </div>
      <pre id="outToday"></pre>

    </div>
  </div>
</div>

<script>
function b() {
  let base = document.getElementById('baseUrl').value.trim();
  if (!base) base = window.location.origin + '/';
  if (!base.endsWith('/')) base += '/';
  return base;
}
function token() { return document.getElementById('apiToken').value.trim(); }
function show(id, data) { document.getElementById(id).textContent = JSON.stringify(data, null, 2); }

async function openVisit() {
  const uid = document.getElementById('uidOpen').value.trim();
  const body = { uid };
  try {
    const r = await fetch(b() + 'index.php/api/visit/open', {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + token(), 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    const js = await r.json(); show('outOpen', js);
  } catch (e) { show('outOpen', { error: String(e) }); }
}

async function uploadFile() {
  const uid = document.getElementById('uidUpload').value.trim();
  const type = document.getElementById('typeUpload').value;
  const note = document.getElementById('noteUpload').value.trim();
  const force = document.getElementById('forceUpload').checked;
  const file = document.getElementById('fileUpload').files[0];
  if (!file) { show('outUpload', { error: 'Please select a file' }); return; }

  const fd = new FormData();
  fd.append('uid', uid);
  fd.append('type', type);
  fd.append('note', note);
  fd.append('forceNewVisit', force ? '1' : '');
  fd.append('file', file);

  try {
    const r = await fetch(b() + 'index.php/api/visit/upload', {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + token() },
      body: fd
    });
    const js = await r.json(); show('outUpload', js);
  } catch (e) { show('outUpload', { error: String(e) }); }
}

async function getToday() {
  const uid = document.getElementById('uidToday').value.trim();
  try {
    const r = await fetch(b() + 'index.php/api/visit/today?uid=' + encodeURIComponent(uid), {
      headers: { 'Authorization': 'Bearer ' + token() }
    });
    const js = await r.json(); show('outToday', js);
  } catch (e) { show('outToday', { error: String(e) }); }
}
</script>
</body>
</html>
