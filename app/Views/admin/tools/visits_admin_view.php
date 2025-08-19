<?php // Minimal AdminLTE‑friendly view; no layout includes to avoid coupling. ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Visits Admin View</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;padding:16px;line-height:1.45}
    .row{display:flex;gap:16px;flex-wrap:wrap}
    .card{border:1px solid #ddd;border-radius:8px;padding:12px;flex:1 1 380px;max-width:680px}
    .grid{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center}
    input,button{font-size:16px;padding:8px;border-radius:6px;border:1px solid #bbb}
    button{cursor:pointer;background:#2563eb;color:#fff;border:0}
    button.secondary{background:#475569}
    .pill{display:inline-block;border:1px solid #ccd;background:#f7f9ff;color:#223;padding:4px 10px;border-radius:18px;margin:0 8px 8px 0;text-decoration:none}
    .pill.active{background:#2563eb;color:#fff;border-color:#2563eb}
    .muted{color:#666;font-size:.9rem}
    .files a{display:inline-block;margin:0 8px 6px 0}
    .json{white-space:pre-wrap;background:#0b1420;color:#d1e7ff;padding:10px;border-radius:6px;max-height:280px;overflow:auto}
  </style>
</head>
<body>

<h2>Visits Admin View</h2>
<p class="muted">Open visit for today, or load by date; click pills to switch Visit # for today. Links open files.</p>

<div class="row">
  <div class="card">
    <div class="grid">
      <label>UID</label>
      <input id="uid" placeholder="250001" value="<?= esc($_GET['uid'] ?? '') ?>">
      <label>Date</label>
      <input id="date" placeholder="dd-mm-yyyy" value="<?= esc($_GET['date'] ?? '') ?>">
      <span></span>
      <div>
        <button id="btnOpen" class="">Open Today</button>
        <button id="btnLoad" class="secondary">Load</button>
      </div>
    </div>
    <div id="pills" style="margin-top:12px"></div>
  </div>

  <div class="card">
    <div id="visits"></div>
  </div>

  <div class="card">
    <div class="json mono small" id="out">[JSON]</div>
  </div>
</div>

<script>
// Force index.php in API paths to match server routing.
const api = {
  open:  '<?= base_url('index.php/api/visit/open'); ?>',
  byDate:'<?= base_url('index.php/api/visit/by-date'); ?>'
};

function ddmmyyyy(date){
  if (!date) return '';
  if (/^\d{2}-\d{2}-\d{4}$/.test(date)) return date;
  if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
    const [Y,M,D] = date.split('-'); return `${D}-${M}-${Y}`;
  }
  return date;
}

function today(){
  const d=new Date(),dd=String(d.getDate()).padStart(2,'0'),mm=String(d.getMonth()+1).padStart(2,'0'),yy=d.getFullYear();
  return `${dd}-${mm}-${yy}`;
}

function setOut(obj){ document.getElementById('out').textContent = JSON.stringify(obj,null,2); }

function renderVisits(data){
  const box = document.getElementById('visits');
  box.innerHTML = '';
  if (!data || !data.ok) { box.textContent = 'No data'; return; }

  (data.results||[]).forEach(v => {
    const wrap = document.createElement('div');
    wrap.style.marginBottom = '14px';
    const t = document.createElement('div');
    t.innerHTML = `<strong>Visit #${v.sequence || v.visit_seq || 0}</strong> — ${v.date}`;
    wrap.appendChild(t);
    const files = document.createElement('div');
    files.className='files';
    (v.documents||[]).forEach(d => {
      const a=document.createElement('a'); a.href=d.url; a.target='_blank'; a.textContent=d.filename || ('doc #'+d.id);
      files.appendChild(a);
    });
    if (!files.children.length) files.textContent='No attachments';
    wrap.appendChild(files);
    box.appendChild(wrap);
  });
  if (!(data.results||[]).length) box.textContent = 'No visits.';
}

function renderPills(data){
  const p = document.getElementById('pills'); p.innerHTML='';
  if (!data || !data.ok) return;
  const arr = data.results || [];
  if (!arr.length) return;

  arr.forEach((v,idx)=>{
    const count = (v.documents||[]).length;
    const a = document.createElement('a');
    a.href='#';
    a.className='pill' + (idx===0?' active':'');
    a.textContent = `Visit #${v.sequence || v.visit_seq || 0} (${count})`;
    a.addEventListener('click', (e)=>{ e.preventDefault(); highlight(idx); renderVisits({ok:true,results:[arr[idx]]}); });
    p.appendChild(a);
  });
  function highlight(i){
    [...p.querySelectorAll('.pill')].forEach((el,ix)=>{ el.classList.toggle('active', ix===i); });
  }
}

async function openToday(){
  const uid = document.getElementById('uid').value.trim();
  const fd = new FormData(); fd.append('uid', uid);
  const res = await fetch(api.open, { method:'POST', body: fd });
  const data = await res.json(); setOut(data);
  await load({uid, date: today()});
}

async function load(opts){
  const uid = (opts&&opts.uid) || document.getElementById('uid').value.trim();
  const rawDate = (opts&&opts.date) || document.getElementById('date').value.trim();
  const date = ddmmyyyy(rawDate);
  const q = new URLSearchParams({ uid, date, all:'1' });
  const res = await fetch(`${api.byDate}?` + q.toString());
  const data = await res.json();
  setOut(data);
  renderPills(data);
  renderVisits(data);
}

document.getElementById('btnOpen').addEventListener('click', openToday);
document.getElementById('btnLoad').addEventListener('click', ()=>load({}));

<?php if (!empty($_GET['uid']) && !empty($_GET['date'])): ?>
load({ uid: '<?= esc($_GET['uid']) ?>', date: '<?= esc($_GET['date']) ?>' });
<?php endif; ?>
</script>
</body>
</html>
