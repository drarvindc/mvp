<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>API Visual Tester — Upload + Visits</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Using Bootstrap only for quick layout; uses your server CSS if present -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 16px; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
    .small-muted { font-size:.85rem; color:#6c757d; }
    .card-title-sm { font-size:1rem; }
    .notes-row { display:flex; gap:6px; align-items:center; margin-top:6px; }
    .notes-row input { flex: 1; }
    .file-list { font-size:.9rem; }
    .file-list li { margin-bottom: 4px; }
    .pill { margin: 0 .25rem .25rem 0; }
    .jsonbox { background:#0b1420; color:#d1e7ff; padding:10px; border-radius:6px; max-height:280px; overflow:auto; }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row g-3">

    <!-- LEFT: Quick actions -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title-sm mb-0">Quick: Open Today</h5>
          </div>
          <div class="mt-3">
            <label class="form-label">UID (6 digits)</label>
            <input id="open_uid" class="form-control" placeholder="250001">
          </div>
          <div class="row mt-3">
            <div class="col-6">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="open_force">
                <label class="form-check-label" for="open_force">Force new visit</label>
              </div>
            </div>
            <div class="col-6">
              <input id="open_token" class="form-control" placeholder="token (optional)">
              <div class="small-muted">DEV_NO_AUTH=true → leave blank</div>
            </div>
          </div>
          <div class="mt-3 d-grid">
            <button class="btn btn-primary" id="btnOpen">Open Today</button>
          </div>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-body">
          <h5 class="card-title-sm mb-0">Quick: By Date</h5>
          <div class="mt-3">
            <label class="form-label">UID</label>
            <input id="by_uid" class="form-control" placeholder="250001">
          </div>
          <div class="mt-2">
            <label class="form-label">Date (dd-mm-yyyy or yyyy-mm-dd)</label>
            <input id="by_date" class="form-control" placeholder="11-08-2025">
          </div>
          <div class="row mt-2">
            <div class="col-6">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="by_all">
                <label class="form-check-label" for="by_all">All visits</label>
              </div>
            </div>
            <div class="col-6">
              <input id="by_token" class="form-control" placeholder="token (optional)">
            </div>
          </div>
          <div class="mt-3 d-grid">
            <button class="btn btn-secondary" id="btnByDate">Load</button>
          </div>
        </div>
      </div>
    </div>

    <!-- CENTER: Upload forms -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title-sm mb-0">Upload (single)</h5>
          <div class="mt-3">
            <label class="form-label">UID</label>
            <input id="u_uid" class="form-control" placeholder="250001">
          </div>
          <div class="mt-2">
            <label class="form-label">Type</label>
            <select id="u_type" class="form-select">
              <option value="rx">rx</option>
              <option value="photo">photo</option>
              <option value="doc">doc</option>
              <option value="xray">xray</option>
              <option value="lab">lab</option>
              <option value="usg">usg</option>
              <option value="invoice">invoice</option>
            </select>
          </div>
          <div class="mt-2">
            <label class="form-label">File</label>
            <input id="u_file" type="file" class="form-control">
          </div>
          <div class="mt-2">
            <label class="form-label">Note (optional)</label>
            <input id="u_note" class="form-control" placeholder="Free text">
          </div>
          <div class="mt-2">
            <input id="u_token" class="form-control" placeholder="token (optional)">
          </div>
          <div class="mt-3 d-grid">
            <button class="btn btn-success" id="btnUploadSingle">Upload Single</button>
          </div>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-body">
          <h5 class="card-title-sm mb-0">Upload (multiple)</h5>
          <div class="mt-3">
            <label class="form-label">UID</label>
            <input id="m_uid" class="form-control" placeholder="250001">
          </div>
          <div class="mt-2">
            <label class="form-label">Type</label>
            <select id="m_type" class="form-select">
              <option value="rx">rx</option>
              <option value="photo">photo</option>
              <option value="doc">doc</option>
              <option value="xray">xray</option>
              <option value="lab">lab</option>
              <option value="usg">usg</option>
              <option value="invoice">invoice</option>
            </select>
          </div>
          <div class="mt-2">
            <label class="form-label">Files</label>
            <input id="m_files" type="file" class="form-control" multiple>
            <ul id="m_filelist" class="mt-2 file-list"></ul>
          </div>
          <div class="mt-2">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="m_oneNote">
              <label class="form-check-label" for="m_oneNote">Use one note for all</label>
            </div>
          </div>
          <div id="m_notes_container" class="mt-2"></div>
          <div class="mt-2">
            <input id="m_token" class="form-control" placeholder="token (optional)">
          </div>
          <div class="mt-3 d-grid">
            <button class="btn btn-success" id="btnUploadMulti">Upload Multiple</button>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Output -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title-sm mb-0">Result</h5>
          <div class="mt-2 jsonbox mono small" id="out">[JSON output]</div>
          <div class="mt-3">
            <h6>Attachment Links</h6>
            <div id="links"></div>
          </div>
        </div>
      </div>
      <div class="small-muted mt-3">
        • DEV_NO_AUTH=true → token not required<br>
        • When auth is enabled, paste your token into the token fields.
      </div>
    </div>

  </div>
</div>

<script>
const apiBase = '<?= site_url('api/visit'); ?>';

function setOut(obj) {
  const out = document.getElementById('out');
  out.textContent = JSON.stringify(obj, null, 2);
  const links = document.getElementById('links');
  links.innerHTML = '';
  if (obj && obj.ok) {
    const arr = [];
    if (obj.attachment && obj.attachment.url) arr.push(obj.attachment);
    if (Array.isArray(obj.attachments)) {
      obj.attachments.forEach(a => { if (a && a.url) arr.push(a); });
    }
    if (Array.isArray(obj.results)) {
      obj.results.forEach(v => {
        (v.documents || []).forEach(d => arr.push(d));
      });
    }
    if (arr.length) {
      const ul = document.createElement('ul');
      ul.className = 'list-unstyled';
      arr.forEach(a => {
        const li = document.createElement('li');
        li.innerHTML = `<a target="_blank" href="${a.url}">${a.filename || a.url}</a>`;
        ul.appendChild(li);
      });
      links.appendChild(ul);
    }
  }
}

function makeQuery(params) {
  const q = new URLSearchParams();
  Object.keys(params).forEach(k => {
    if (params[k] !== undefined && params[k] !== null && params[k] !== '') {
      q.append(k, params[k]);
    }
  });
  return q.toString();
}

document.getElementById('btnOpen').addEventListener('click', async () => {
  const uid = document.getElementById('open_uid').value.trim();
  const force = document.getElementById('open_force').checked;
  const token = document.getElementById('open_token').value.trim();
  const q = token ? '?token=' + encodeURIComponent(token) : '';
  const fd = new FormData();
  fd.append('uid', uid);
  if (force) fd.append('forceNewVisit', '1');
  const res = await fetch(`${apiBase}/open${q}`, { method:'POST', body: fd });
  setOut(await res.json());
});

document.getElementById('btnByDate').addEventListener('click', async () => {
  const uid = document.getElementById('by_uid').value.trim();
  const date = document.getElementById('by_date').value.trim();
  const all = document.getElementById('by_all').checked ? '1' : '';
  const token = document.getElementById('by_token').value.trim();
  const qs = makeQuery({ uid, date, all, token });
  const res = await fetch(`${apiBase}/by-date?${qs}`);
  setOut(await res.json());
});

const mFiles = document.getElementById('m_files');
const mList = document.getElementById('m_filelist');
const mNotesContainer = document.getElementById('m_notes_container');
const mOneNote = document.getElementById('m_oneNote');

function rebuildNotesUI() {
  mNotesContainer.innerHTML = '';
  mList.innerHTML = '';

  const files = Array.from(mFiles.files || []);
  files.forEach((f, idx) => {
    const li = document.createElement('li');
    li.textContent = `${idx+1}. ${f.name} (${f.size} bytes)`;
    mList.appendChild(li);
  });

  if (mOneNote.checked) {
    const row = document.createElement('div');
    row.className = 'notes-row';
    row.innerHTML = `<input class="form-control" placeholder="One note applied to all files" id="m_note_all">`;
    mNotesContainer.appendChild(row);
  } else {
    files.forEach((f, idx) => {
      const row = document.createElement('div');
      row.className = 'notes-row';
      row.innerHTML = `<span class="text-muted small">${idx+1}.</span><input class="form-control" placeholder="Note for ${f.name}" data-idx="${idx}">`;
      mNotesContainer.appendChild(row);
    });
  }
}
mFiles.addEventListener('change', rebuildNotesUI);
mOneNote.addEventListener('change', rebuildNotesUI);

document.getElementById('btnUploadSingle').addEventListener('click', async () => {
  const uid = document.getElementById('u_uid').value.trim();
  const type = document.getElementById('u_type').value;
  const token = document.getElementById('u_token').value.trim();
  const fileEl = document.getElementById('u_file');
  const note = document.getElementById('u_note').value;

  const fd = new FormData();
  fd.append('uid', uid);
  fd.append('type', type);
  if (fileEl.files[0]) fd.append('file', fileEl.files[0]);
  if (note) fd.append('note', note);

  const q = token ? '?token=' + encodeURIComponent(token) : '';
  const res = await fetch(`${apiBase}/upload${q}`, { method:'POST', body: fd });
  setOut(await res.json());
});

document.getElementById('btnUploadMulti').addEventListener('click', async () => {
  const uid = document.getElementById('m_uid').value.trim();
  const type = document.getElementById('m_type').value;
  const token = document.getElementById('m_token').value.trim();

  const fd = new FormData();
  fd.append('uid', uid);
  fd.append('type', type);

  const files = Array.from(document.getElementById('m_files').files || []);
  files.forEach(f => fd.append('file[]', f));

  if (document.getElementById('m_oneNote').checked) {
    const one = (document.getElementById('m_note_all') || {}).value || '';
    if (one) fd.append('note', one);
  } else {
    const noteInputs = Array.from(mNotesContainer.querySelectorAll('input[data-idx]'));
    noteInputs.forEach(inp => fd.append('note[]', inp.value || ''));
  }

  const q = token ? '?token=' + encodeURIComponent(token) : '';
  const res = await fetch(`${apiBase}/upload${q}`, { method:'POST', body: fd });
  setOut(await res.json());
});
</script>
</body>
</html>
