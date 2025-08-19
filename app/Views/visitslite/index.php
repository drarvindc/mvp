<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Visits Lite</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;padding:16px}
    .row{display:flex;gap:16px;flex-wrap:wrap}
    .card{border:1px solid #ddd;border-radius:8px;padding:12px;flex:1 1 380px;max-width:640px}
    .mono{font-family:ui-monospace,Menlo,Consolas,monospace}
    .pill{display:inline-block;background:#eef;padding:4px 8px;border-radius:20px;margin:0 6px 6px 0;text-decoration:none;color:#223}
    .muted{color:#666;font-size:.9rem}
    .grid{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center}
    .json{white-space:pre-wrap;background:#0b1420;color:#d1e7ff;padding:10px;border-radius:6px;max-height:260px;overflow:auto}
    label{font-weight:600}
    input,select,button{font-size:16px;padding:8px;border-radius:6px;border:1px solid #bbb}
    button{cursor:pointer;background:#2563eb;color:#fff;border:0}
    button.secondary{background:#475569}
    .files a{display:inline-block;margin:0 8px 6px 0}
  </style>
</head>
<body>
  <h2>Visits Lite</h2>
  <p class="muted">Quick, public view to list same‑day visits + attachments. Supports <span class="mono">uid</span> with either <span class="mono">today=1</span> or a specific <span class="mono">date</span> (dd-mm-yyyy or yyyy-mm-dd). Optional: <span class="mono">all=1</span> for all visits that day.</p>

  <div class="row">
    <div class="card">
      <div class="grid">
        <label for="uid">UID</label>
        <input id="uid" placeholder="250001" />
        <label for="date">Date</label>
        <input id="date" placeholder="dd-mm-yyyy or yyyy-mm-dd" />
        <span></span>
        <div>
          <label><input type="checkbox" id="today" /> today</label>
          &nbsp;&nbsp;
          <label><input type="checkbox" id="all" /> all visits</label>
        </div>
        <span></span>
        <div>
          <button id="btnLoad" class="">Load</button>
          <button id="btnToday" class="secondary">Load Today</button>
        </div>
      </div>
    </div>

    <div class="card">
      <div id="visits"></div>
    </div>

    <div class="card">
      <div class="json mono small" id="out">[JSON]</div>
    </div>
  </div>

<script>
const api = {
  byDate: '<?= site_url('api/visit/by-date'); ?>'
};

function fmtDate(d) {
  // accepts dd-mm-yyyy or yyyy-mm-dd, returns dd-mm-yyyy
  if (!d) return '';
  if (/^\d{2}-\d{2}-\d{4}$/.test(d)) return d;
  if (/^\d{4}-\d{2}-\d{2}$/.test(d)) {
    const [Y,M,D] = d.split('-'); return `${D}-${M}-${Y}`;
  }
  return d;
}

function todayDDMMYYYY(){
  const t = new Date();
  const dd = String(t.getDate()).padStart(2,'0');
  const mm = String(t.getMonth()+1).padStart(2,'0');
  const yyyy = t.getFullYear();
  return `${dd}-${mm}-${yyyy}`;
}

function setOut(obj){ document.getElementById('out').textContent = JSON.stringify(obj,null,2); }

function render(data){
  const box = document.getElementById('visits');
  box.innerHTML = '';
  if (!data || !data.ok) { box.textContent = 'No data'; return; }
  const list = [];
  (data.results||[]).forEach(v => {
    const h = document.createElement('div');
    h.style.marginBottom = '14px';
    const title = document.createElement('div');
    title.innerHTML = `<strong>Visit #${v.sequence || v.visit_seq || 0}</strong> — ${v.date}`;
    h.appendChild(title);
    const files = document.createElement('div');
    files.className = 'files';
    (v.documents||[]).forEach(d => {
      const a = document.createElement('a');
      a.href = d.url;
      a.target = '_blank';
      a.textContent = d.filename || ('doc #'+d.id);
      files.appendChild(a);
    });
    if (!files.children.length) files.textContent = 'No attachments';
    h.appendChild(files);
    box.appendChild(h);
  });
  if (!(data.results||[]).length) box.textContent = 'No visits found for given UID/date.';
}

async function load(opts){
  const uid = (opts && opts.uid) || document.getElementById('uid').value.trim();
  const today = (opts && 'today' in opts) ? opts.today : document.getElementById('today').checked;
  const date = (opts && 'date' in opts) ? opts.date : document.getElementById('date').value.trim();
  const all  = (opts && 'all' in opts) ? opts.all : document.getElementById('all').checked;

  const q = new URLSearchParams();
  if (uid) q.append('uid', uid);
  if (today) q.append('today', '1');
  const ddmmyyyy = today ? todayDDMMYYYY() : fmtDate(date);
  if (!today && ddmmyyyy) q.append('date', ddmmyyyy);
  if (all) q.append('all','1');

  const res = await fetch(`${api.byDate}?` + q.toString());
  const data = await res.json();
  setOut(data);
  render(data);
}

document.getElementById('btnLoad').addEventListener('click', () => load({}));
document.getElementById('btnToday').addEventListener('click', () => load({ today:true }));
</script>
</body>
</html>
