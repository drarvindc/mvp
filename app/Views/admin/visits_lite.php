<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Visits (Lite)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 16px; }
    .row { margin-bottom: 12px; }
    .pillbar { display: flex; gap: 8px; flex-wrap: wrap; margin: 8px 0 16px; }
    .pill { padding: 8px 12px; border: 1px solid #ccc; border-radius: 999px; cursor: pointer; user-select: none; }
    .pill.active { background: #eee; border-color: #888; }
    .list { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
    .item { display: flex; justify-content: space-between; padding: 10px 12px; border-bottom: 1px solid #eee; }
    .item:last-child { border-bottom: 0; }
    .muted { color: #666; font-size: 12px; }
    .err { color: #b00020; }
    .head { font-weight: 600; margin-bottom: 6px; }
    input[type=text] { padding: 8px; border: 1px solid #ccc; border-radius: 6px; width: 220px; }
    button { padding: 8px 12px; border: 1px solid #333; background: #fff; border-radius: 6px; cursor: pointer; }
    button:disabled { opacity: .5; cursor: not-allowed; }
  </style>
</head>
<body>
  <div class="row">
    <div class="head">Visits (Lite)</div>
    <div class="muted">Use this page if <code>/admin/visits</code> redirects due to an auth filter. It fetches data from <code>/api/visit/today?all=1</code>.</div>
  </div>

  <div class="row">
    <label>UID: <input type="text" id="uid" value="<?= esc($uid) ?>"></label>
    <label style="margin-left:8px;">Date (YYYY-MM-DD): <input type="text" id="date" value="<?= esc($date) ?>"></label>
    <button id="go">Load</button>
    <span id="msg" class="muted"></span>
  </div>

  <div id="out"></div>

<script>
(function(){
  const base = "<?= esc($base) ?>";
  const goBtn = document.getElementById('go');
  const msg = document.getElementById('msg');
  const out = document.getElementById('out');

  async function load() {
    const uid = document.getElementById('uid').value.trim();
    const date = document.getElementById('date').value.trim();
    if (!uid) { msg.textContent = 'Enter UID'; return; }

    msg.textContent = 'Loading...';
    out.innerHTML = '';

    const qs = new URLSearchParams({ uid: uid, all: '1' });
    if (date) qs.set('date', date); // if your API supports date param; ignored otherwise

    try {
      const res = await fetch(`${base}/index.php/api/visit/today?${qs.toString()}`, { credentials: 'same-origin' });
      const data = await res.json();
      if (!data || data.ok !== true) {
        msg.textContent = (data && data.error) ? data.error : 'Failed';
        return;
      }
      msg.textContent = '';
      render(data.results || []);
    } catch (e) {
      console.error(e);
      msg.textContent = 'Fetch error';
    }
  }

  function render(visits) {
    if (!visits.length) {
      out.innerHTML = '<div class="muted">No visits found for the selected date.</div>';
      return;
    }

    // Pills
    const bar = document.createElement('div');
    bar.className = 'pillbar';
    visits.forEach((v, i) => {
      const p = document.createElement('div');
      p.className = 'pill' + (i === 0 ? ' active' : '');
      const label = v.sequence > 1 ? `Visit #${v.sequence} (PM)` : 'Visit #1 (AM)';
      p.textContent = label;
      p.dataset.idx = i;
      p.addEventListener('click', () => {
        [...bar.children].forEach(c => c.classList.remove('active'));
        p.classList.add('active');
        showVisit(visits[i]);
      });
      bar.appendChild(p);
    });
    out.appendChild(bar);

    const container = document.createElement('div');
    out.appendChild(container);

    function showVisit(v) {
      container.innerHTML = '';
      const title = document.createElement('div');
      title.className = 'head';
      title.textContent = `Attachments for ${v.date} • Visit #${v.sequence}`;
      container.appendChild(title);

      if (!v.attachments || !v.attachments.length) {
        const empty = document.createElement('div');
        empty.className = 'muted';
        empty.textContent = 'No attachments.';
        container.appendChild(empty);
        return;
      }

      const list = document.createElement('div');
      list.className = 'list';
      v.attachments.forEach(a => {
        const row = document.createElement('div');
        row.className = 'item';
        const left = document.createElement('div');
        left.innerHTML = `<div>${a.type || 'file'} — <strong>${a.filename}</strong></div>
                          <div class="muted">#${a.id} • ${a.created_at}</div>`;
        const right = document.createElement('div');
        const link = document.createElement('a');
        link.href = `${base}/index.php/admin/visit/file?id=${a.id}`;
        link.target = '_blank';
        link.textContent = 'Open';
        right.appendChild(link);
        row.appendChild(left);
        row.appendChild(right);
        list.appendChild(row);
      });
      container.appendChild(list);
    }

    showVisit(visits[0]);
  }

  goBtn.addEventListener('click', load);

  // Auto-load if UID prefilled
  if (document.getElementById('uid').value.trim()) {
    load();
  }
})();
</script>
</body>
</html>
