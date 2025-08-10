<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Android API Tester — Upload</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin: 16px; }
    h2 { margin: 0 0 12px; }
    .card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; margin: 12px 0; }
    label { display:block; font-weight:600; margin-top:8px; }
    input[type="text"], input[type="date"], input[type="file"], select, textarea { width:100%; padding:8px; box-sizing:border-box; }
    button { padding:10px 14px; margin-top:10px; }
    .row2 { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
    .muted { color:#666; }
    pre { background:#111; color:#0f0; padding:10px; border-radius:6px; overflow:auto; max-height:40vh; }
    .inline { display:flex; align-items:center; gap:8px; margin-top:8px; }
  </style>
</head>
<body>
  <h2>Android API Tester — Upload</h2>
  <div class="muted">Keyed access OK. Use this to test from your phone. This page does not require admin login.</div>

  <div class="card">
    <h3>1) Open Visit (create or reuse today)</h3>
    <form id="formOpen" onsubmit="return sendOpen(event)">
      <label>UID</label>
      <input name="uid" id="open_uid" placeholder="e.g. 250001" required>
      <label class="inline"><input type="checkbox" name="forceNewVisit" id="open_force" value="1"> forceNewVisit</label>
      <button type="submit">POST /api/visit/open</button>
    </form>
    <pre id="outOpen">Ready.</pre>
  </div>

  <div class="card">
    <h3>2) Upload Document (tests Android file picker)</h3>
    <form id="formUpload" onsubmit="return sendUpload(event)">
      <label>UID</label>
      <input name="uid" id="up_uid" placeholder="e.g. 250001" required>

      <label>Type (rx / lab / img / other)</label>
      <input name="type" id="up_type" placeholder="rx">

      <label>Visit ID (optional — leave blank to attach to latest today or auto-create)</label>
      <input name="visitId" id="up_visitId" placeholder="">

      <div class="inline">
        <label class="inline"><input type="checkbox" name="backfill" id="up_backfill" value="1" checked> backfill orphan docs to latest visit</label>
      </div>

      <label>File</label>
      <input type="file" name="file" id="up_file" required accept="image/*,application/pdf">

      <button type="submit">POST /api/visit/upload</button>
    </form>
    <pre id="outUpload">Ready.</pre>
  </div>

  <div class="card">
    <h3>3) Today (list visits + docs)</h3>
    <form id="formToday" onsubmit="return sendToday(event)">
      <div class="row2">
        <div>
          <label>UID</label>
          <input name="uid" id="tod_uid" required>
        </div>
        <div>
          <label>Date</label>
          <input type="date" name="date" id="tod_date" value="<?= date('Y-m-d') ?>">
        </div>
      </div>
      <label class="inline"><input type="checkbox" name="all" id="tod_all" value="1" checked> all=1</label>
      <button type="submit">GET /api/visit/today</button>
    </form>
    <pre id="outToday">Ready.</pre>
  </div>

<script>
const siteOpen = "<?= $siteOpen ?>";
const siteUpload = "<?= $siteUpload ?>";
const siteToday = "<?= $siteToday ?>";
function setOut(el, data){ el.textContent = (typeof data === 'string') ? data : JSON.stringify(data, null, 2); }

async function sendOpen(e){
  e.preventDefault();
  const out = document.getElementById('outOpen');
  out.textContent = 'Sending...';
  const fd = new FormData();
  fd.append('uid', document.getElementById('open_uid').value.trim());
  if (document.getElementById('open_force').checked) fd.append('forceNewVisit', '1');
  try{
    const res = await fetch(siteOpen, { method:'POST', body: fd });
    const data = await res.json();
    setOut(out, data);
  }catch(err){ setOut(out, 'Error: '+err.message); }
  return false;
}

async function sendUpload(e){
  e.preventDefault();
  const out = document.getElementById('outUpload');
  out.textContent = 'Uploading...';
  const fd = new FormData();
  fd.append('uid', document.getElementById('up_uid').value.trim());
  const t = document.getElementById('up_type').value.trim();
  if (t) fd.append('type', t);
  const v = document.getElementById('up_visitId').value.trim();
  if (v) fd.append('visitId', v);
  if (document.getElementById('up_backfill').checked) fd.append('backfill', '1');
  const file = document.getElementById('up_file').files[0];
  if (!file){ setOut(out, 'Please choose a file'); return false; }
  fd.append('file', file);

  try{
    const res = await fetch(siteUpload, { method:'POST', body: fd });
    const text = await res.text();
    try { setOut(out, JSON.parse(text)); } catch(e){ setOut(out, text); }
  }catch(err){ setOut(out, 'Error: '+err.message); }
  return false;
}

async function sendToday(e){
  e.preventDefault();
  const out = document.getElementById('outToday');
  out.textContent = 'Loading...';
  const uid = encodeURIComponent(document.getElementById('tod_uid').value.trim());
  const date = encodeURIComponent(document.getElementById('tod_date').value);
  const all = document.getElementById('tod_all').checked ? '&all=1' : '';
  const url = siteToday + '?uid=' + uid + '&date=' + date + all;
  try{
    const res = await fetch(url);
    const data = await res.json();
    setOut(out, data);
  }catch(err){ setOut(out, 'Error: '+err.message); }
  return false;
}
</script>
</body>
</html>
