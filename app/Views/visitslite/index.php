<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Visits Lite</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 20px; }
    .pill { display:inline-block; padding:8px 14px; border:1px solid #ccc; border-radius:999px; margin-right:8px; cursor:pointer; }
    .pill.active { background:#eee; }
    .muted { color:#666; }
    .row { margin: 10px 0; }
    .cards { display:flex; flex-wrap:wrap; gap:10px; }
    .card { border:1px solid #ddd; border-radius:8px; padding:10px; width:300px; }
    a.button { display:inline-block; padding:6px 10px; border:1px solid #333; border-radius:6px; text-decoration:none; }
  </style>
</head>
<body>
  <h2>Visits – quick view</h2>
  <div class="row">
    <label>UID: <input id="uid" value="<?= esc($this->request->getGet('uid') ?? '') ?>"></label>
    <label>Date: <input id="date" type="date" value="<?= esc($this->request->getGet('date') ?? date('Y-m-d')) ?>"></label>
    <button id="load">Load</button>
  </div>
  <div id="pills" class="row"></div>
  <div id="content" class="cards"></div>

<script>
async function fetchToday(uid, date) {
  const url = `<?= site_url('api/visit/today'); ?>?uid=${encodeURIComponent(uid)}&date=${encodeURIComponent(date)}&all=1`;
  const resp = await fetch(url);
  return await resp.json();
}
function render(data){
  const pills = document.getElementById('pills');
  const content = document.getElementById('content');
  pills.innerHTML = ''; content.innerHTML='';
  if(!data.ok || !data.results || data.results.length===0){
    content.innerHTML = '<div class="muted">No visits found for the given UID/date.</div>';
    return;
  }
  data.results.forEach((v,idx)=>{
    const p = document.createElement('span');
    p.className = 'pill' + (idx===0?' active':'');
    p.textContent = 'Visit #' + v.sequence + (v.sequence===1?' (AM)':' (PM)');
    p.onclick = ()=>{
      document.querySelectorAll('.pill').forEach(x=>x.classList.remove('active'));
      p.classList.add('active');
      showVisit(v);
    };
    pills.appendChild(p);
  });
  showVisit(data.results[0]);
}
function showVisit(v){
  const content = document.getElementById('content');
  content.innerHTML = '';
  const card = document.createElement('div');
  card.className = 'card';
  const list = (v.attachments||[]).map(a=>{
    const href = `<?= site_url('admin/visit/file'); ?>?id=${encodeURIComponent(a.id)}`;
    return `<li>${a.type||'doc'} — <a class="button" href="${href}" target="_blank">Open</a> <span class="muted">${a.filename||''}</span></li>`;
  }).join('');
  card.innerHTML = `<div><strong>Date:</strong> ${v.date}</div><div><strong>Sequence:</strong> #${v.sequence}</div><div><strong>Attachments</strong><ul>${list||'<li class=muted>None</li>'}</ul></div>`;
  content.appendChild(card);
}
document.getElementById('load').onclick = async ()=>{
  const uid = document.getElementById('uid').value.trim();
  const date = document.getElementById('date').value;
  const data = await fetchToday(uid, date);
  render(data);
};
// auto load if params present
const paramUid = document.getElementById('uid').value.trim();
if(paramUid){ document.getElementById('load').click(); }
</script>
</body>
</html>
