<!doctype html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Settings</title>
<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;padding:16px}
label{font-weight:600}input{transform:scale(1.2);margin-right:8px}</style>
</head><body>
<h2>Settings</h2>
<p><label><input type="checkbox" id="auto"> Auto‑map orphan docs on upload</label>
<span id="state" style="margin-left:8px;color:#666"></span></p>
<script>
(async function(){
  const auto = document.getElementById('auto'), st = document.getElementById('state');
  async function refresh(){
    const r = await fetch('<?= site_url('admin/tools/settings'); ?>'); const j = await r.json();
    auto.checked = (j.auto_map_orphans === '1'); st.textContent = auto.checked ? '(enabled)' : '(disabled)';
  }
  auto.addEventListener('change', async ()=>{
    const fd = new FormData(); fd.append('enable', auto.checked ? '1' : '0');
    const r = await fetch('<?= site_url('admin/tools/settings/toggle'); ?>', { method:'POST', body: fd });
    const j = await r.json(); st.textContent = (j.auto_map_orphans === '1') ? '(enabled)' : '(disabled)';
  });
  refresh();
})();
</script>
</body></html>
