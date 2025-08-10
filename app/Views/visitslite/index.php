<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Visits (Lite)</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:16px}
    .row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
    .pills{display:flex;gap:8px;margin:12px 0;flex-wrap:wrap}
    .pill{padding:8px 12px;border:1px solid #ddd;border-radius:999px;cursor:pointer;user-select:none}
    .pill.active{border-color:#000;font-weight:600}
    .card{border:1px solid #eee;border-radius:8px;padding:12px;margin:8px 0}
    .muted{color:#666;font-size:12px}
    table{width:100%;border-collapse:collapse;margin-top:8px}
    th,td{border-bottom:1px solid #eee;padding:8px;text-align:left}
    input,button{padding:8px 10px}
    .err{color:#b00020;margin-top:8px}
  </style>
</head>
<body>
  <h2>Visits (Lite)</h2>
  <div class="row">
    <label>UID: <input id="uid" placeholder="250001"></label>
    <label>Date: <input id="date" type="date"></label>
    <button id="loadBtn">Load</button>
  </div>
  <div id="status" class="muted">Enter UID and Date, then Load.</div>
  <div id="pills" class="pills"></div>
  <div id="content"></div>

<script>
(function(){
  const q = new URLSearchParams(location.search);
  const uidInput = document.getElementById('uid');
  const dateInput = document.getElementById('date');
  uidInput.value = q.get('uid') || '';
  dateInput.value = q.get('date') || new Date().toISOString().slice(0,10);

  document.getElementById('loadBtn').addEventListener('click', () => load());

  async function load(){
    const uid = uidInput.value.trim();
    const date = dateInput.value;
    const status = document.getElementById('status');
    const pills = document.getElementById('pills');
    const content = document.getElementById('content');
    pills.innerHTML = '';
    content.innerHTML = '';
    if(!uid){ status.textContent = 'Please enter UID.'; return; }
    status.textContent = 'Loading...';

    try{
      // We assume /api/visit/today exists and supports &all=1 and respects the provided date if present.
      // If your today() endpoint ignores date, we only use uid+all=1.
      const params = new URLSearchParams({ uid, all: '1' });
      const resp = await fetch('/index.php/api/visit/today?' + params.toString(), { credentials: 'same-origin' });
      if(!resp.ok){
        status.textContent = 'API error: ' + resp.status;
        return;
      }
      const data = await resp.json();
      if(!data.ok){ status.textContent = 'Server says: ' + (data.error || 'Unknown error'); return; }

      const visits = (data.results || []).filter(v => !date || v.date === date);
      if(visits.length === 0){
        status.textContent = 'No visits found for the given UID/date.';
        return;
      }
      status.textContent = 'Loaded ' + visits.length + ' visit(s) on ' + visits[0].date;

      // Build pills
      visits.forEach((v, i) => {
        const pill = document.createElement('div');
        pill.className = 'pill' + (i===0 ? ' active' : '');
        const label = 'Visit #' + v.sequence + (v.sequence===1?' (AM)':'');
        pill.textContent = label;
        pill.addEventListener('click', () => {
          document.querySelectorAll('.pill').forEach(x=>x.classList.remove('active'));
          pill.classList.add('active');
          render(v);
        });
        pills.appendChild(pill);
      });

      render(visits[0]);

      function render(v){
        content.innerHTML = '';
        const h = document.createElement('div');
        h.className = 'card';
        h.innerHTML = '<div><strong>Date:</strong> '+v.date+' | <strong>Sequence:</strong> '+v.sequence+'</div>';
        content.appendChild(h);

        const atts = v.attachments || [];
        if(atts.length === 0){
          const e = document.createElement('div');
          e.className = 'muted';
          e.textContent = 'No attachments for this visit.';
          content.appendChild(e);
          return;
        }

        const table = document.createElement('table');
        table.innerHTML = '<thead><tr><th>ID</th><th>Type</th><th>Filename</th><th>Size</th><th>Opened</th></tr></thead>';
        const tb = document.createElement('tbody');
        atts.forEach(a => {
          const tr = document.createElement('tr');
          const url = '/index.php/admin/visit/file?id=' + a.id;
          tr.innerHTML = '<td>'+a.id+'</td><td>'+a.type+'</td><td>'+a.filename+'</td><td>'+a.filesize+'</td><td><a href="'+url+'" target="_blank">Open</a></td>';
          tb.appendChild(tr);
        });
        table.appendChild(tb);
        content.appendChild(table);
      }
    } catch(err){
      console.error(err);
      status.textContent = 'Unexpected error. See console.';
    }
  }

  // Auto-load if uid provided via querystring
  if(uidInput.value){ load(); }
})();
</script>
</body>
</html>
