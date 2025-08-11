<?php
// app/Views/visitslite/index.php
$apiToken = env('ANDROID_API_TOKEN');
$uid  = $_GET['uid']  ?? '';
$date = $_GET['date'] ?? date('Y-m-d'); // browser date input default
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Visits Lite</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .pill { padding: .4rem .9rem; border:1px solid #ced4da; border-radius: 999px; background:#f8f9fa; cursor:pointer; }
    .pill.active { background:#e9ecef; border-color:#adb5bd; }
  </style>
</head>
<body class="container py-3">
  <h4 class="mb-3">Visits Lite</h4>

  <div class="row g-2 align-items-end">
    <div class="col-auto">
      <label class="form-label">UID</label>
      <input id="uid" class="form-control" value="<?= htmlspecialchars($uid, ENT_QUOTES) ?>" />
    </div>
    <div class="col-auto">
      <label class="form-label">Date</label>
      <input id="date" class="form-control" type="date" value="<?= htmlspecialchars($date, ENT_QUOTES) ?>" />
      <div class="form-text">Displayed & API use: dd-mm-yyyy</div>
    </div>
    <div class="col-auto">
      <button id="loadBtn" class="btn btn-primary">Load</button>
    </div>
  </div>

  <div id="status" class="text-muted mt-3">Enter UID and Date, then Load.</div>
  <div id="pills" class="d-flex gap-2 mt-2 flex-wrap"></div>
  <div id="content" class="mt-3"></div>

  <script>
  const TOKEN = <?= json_encode($apiToken) ?>; // from .env

  function q(s){ return document.querySelector(s); }
  function ce(t){ return document.createElement(t); }

  function isoToDMY(s){
    // yyyy-mm-dd -> dd-mm-yyyy
    const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(s);
    if (m) return `${m[3]}-${m[2]}-${m[1]}`;
    // already dd-mm-yyyy or other
    const n = /^(\d{2})-(\d{2})-(\d{4})$/.exec(s);
    if (n) return s;
    // last resort
    const d = new Date(s);
    if (!isNaN(d)) {
      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yy = d.getFullYear();
      return `${dd}-${mm}-${yy}`;
    }
    return s;
  }

  async function fetchVisits(uid, dmy) {
    const base = <?= json_encode(site_url('api/visit/today')) ?>;
    const url = `${base}?uid=${encodeURIComponent(uid)}&date=${encodeURIComponent(dmy)}&all=1&token=${encodeURIComponent(TOKEN)}`;
    const res = await fetch(url, { headers: { 'Accept':'application/json' } });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  function render(data) {
    const pills = q('#pills'); pills.innerHTML = '';
    const content = q('#content'); content.innerHTML = '';

    if (!data.ok || !data.results || data.results.length === 0) {
      q('#status').textContent = 'No visits found for the given UID/date.';
      return;
    }
    q('#status').textContent = `Found ${data.results.length} visit(s) for ${data.date}`;

    data.results.forEach((v, idx) => {
      const p = ce('button');
      p.className = 'pill' + (idx===0 ? ' active' : '');
      p.textContent = `Visit #${v.sequence}` + (v.sequence === 1 ? ' (AM)' : (v.sequence === 2 ? ' (PM)' : ''));
      p.addEventListener('click', () => {
        document.querySelectorAll('.pill').forEach(el => el.classList.remove('active'));
        p.classList.add('active');
        renderVisit(v);
      });
      pills.appendChild(p);
    });

    renderVisit(data.results[0]);

    function renderVisit(v) {
      content.innerHTML = '';
      const head = ce('div');
      head.className = 'card card-body';
      head.innerHTML = `<strong>Visit #${v.sequence}</strong> · ${v.date}`;
      content.appendChild(head);

      if (!v.documents || v.documents.length === 0) {
        const empty = ce('div');
        empty.className = 'text-muted mt-2';
        empty.textContent = 'No documents for this visit.';
        content.appendChild(empty);
      } else {
        v.documents.forEach(d => {
          const card = ce('div');
          card.className = 'card card-body mt-2';
          const size = d.filesize ? `${d.filesize} bytes` : '';
          const link = d.url || '#';
          card.innerHTML = `<div><strong>${d.type || 'file'}</strong> — ${d.filename || ''}</div>
                            <div class="text-muted small">${(d.created_at || '')} ${size ? ' · '+size : ''}</div>
                            <div class="mt-2"><a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="${link}">Open</a></div>
                            <div class="text-muted small mt-1">If this redirects, your file endpoint may require admin login.</div>`;
          content.appendChild(card);
        });
      }
    }
  }

  q('#loadBtn').addEventListener('click', async () => {
    const uid = q('#uid').value.trim();
    const iso = q('#date').value.trim(); // yyyy-mm-dd from input
    const dmy = isoToDMY(iso);
    q('#status').textContent = 'Loading...';
    try {
      const data = await fetchVisits(uid, dmy);
      render(data);
    } catch (e) {
      q('#status').textContent = 'Error: ' + e.message;
    }
  });

  // auto-load if uid prefilled
  if (q('#uid').value) q('#loadBtn').click();
  </script>
</body>
</html>
