<?php
/** @var string $uid */
/** @var string $date */
$uid  = isset($uid) ? $uid : '';
$date = isset($date) ? $date : date('Y-m-d');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Visits Lite</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; margin: 16px; }
    .row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .pillbar { display: flex; gap: 8px; margin: 12px 0; }
    .pill { padding: 8px 12px; border: 1px solid #ccc; border-radius: 999px; cursor: pointer; }
    .pill.active { background: #eee; border-color: #999; }
    .card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; margin: 8px 0; }
    .muted { color: #666; }
    a.button { display: inline-block; padding: 6px 10px; border: 1px solid #888; border-radius: 6px; text-decoration: none; }
    label { font-weight: 600; }
    input, button { padding: 6px 8px; }
  </style>
</head>
<body>
  <h2>Visits Lite</h2>

  <div class="row">
    <label>UID:</label>
    <input id="uid" value="<?= htmlspecialchars($uid, ENT_QUOTES) ?>" />
    <label>Date:</label>
    <input id="date" type="date" value="<?= htmlspecialchars($date, ENT_QUOTES) ?>" />
    <button id="load">Load</button>
  </div>

  <div id="status" class="muted">Enter UID and Date, then Load.</div>
  <div id="pills" class="pillbar"></div>
  <div id="content"></div>

  <script>
  function q(sel){ return document.querySelector(sel); }
  function ce(tag){ return document.createElement(tag); }

  async function fetchVisits(uid, date) {
    const url = `<?= site_url('api/visit/today') ?>?uid=${encodeURIComponent(uid)}&date=${encodeURIComponent(date)}&all=1`;
    const res = await fetch(url);
    if (!res.ok) throw new Error('HTTP '+res.status);
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
      head.className = 'card';
      head.innerHTML = `<strong>Visit #${v.sequence}</strong> · ${v.date}`;
      content.appendChild(head);

      if (!v.documents || v.documents.length === 0) {
        const empty = ce('div');
        empty.className = 'muted';
        empty.textContent = 'No documents for this visit.';
        content.appendChild(empty);
      } else {
        v.documents.forEach(d => {
          const card = ce('div');
          card.className = 'card';
          card.innerHTML = `<div><strong>${d.type || 'file'}</strong> — ${d.filename || ''}</div>
                            <div class="muted">${d.created_at || ''} · ${d.filesize || ''} bytes</div>
                            <div style="margin-top:6px"><a class="button" target="_blank" href="${d.url}">Open</a></div>`;
          content.appendChild(card);
        });
      }
    }
  }

  q('#load').addEventListener('click', async () => {
    const uid = q('#uid').value.trim();
    const date = q('#date').value.trim();
    q('#status').textContent = 'Loading...';
    try {
      const data = await fetchVisits(uid, date);
      render(data);
    } catch (e) {
      q('#status').textContent = 'Error: ' + e.message;
    }
  });

  // auto-load if uid prefilled
  if (q('#uid').value) q('#load').click();
  </script>
</body>
</html>
