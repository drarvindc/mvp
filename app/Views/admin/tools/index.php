<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Tools</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4">Admin Tools</h1>
        <ul class="list-group">
            <li><a class="list-group-item" href="<?= site_url('admin/tools/migrate') ?>">Run Migration</a></li>
            <li><a class="list-group-item" href="<?= site_url('admin/tools/api-tester') ?>">API Tester</a></li>
            <li><a class="list-group-item" href="<?= site_url('admin/tools/api-tester-android') ?>">Android Upload Tester</a></li>
            <li><a class="list-group-item" href="<?= site_url('admin/tools/api-tester-classic') ?>">Classic Upload Tester</a></li>
            <li><a class="list-group-item" href="<?= site_url('admin/tools/db-check') ?>">DB Check</a></li>

            <!-- New link added -->
            <li><a class="list-group-item" href="<?= site_url('admin/tools/visits-admin-view') ?>">Visits Admin View</a></li>
<li><a href="<?= site_url('admin/tools/upload-tester-multi'); ?>">Visual Upload Tester (multi)</a></li>


        </ul>
    </div>
	<hr>
<h3>Settings</h3>
<div id="autoMapRow">
  <label>
    <input type="checkbox" id="autoMapChk"> Auto‑map orphan docs on upload
  </label>
  <span id="autoMapState" style="margin-left:8px;color:#666"></span>
</div>
<script>
(async function(){
  try {
    const r = await fetch('<?= site_url('admin/tools/settings'); ?>');
    const j = await r.json();
    const chk = document.getElementById('autoMapChk');
    const st  = document.getElementById('autoMapState');
    chk.checked = (j.auto_map_orphans === '1');
    st.textContent = chk.checked ? '(enabled)' : '(disabled)';
    chk.addEventListener('change', async ()=>{
      const fd = new FormData(); fd.append('enable', chk.checked ? '1' : '0');
      const rr = await fetch('<?= site_url('admin/tools/settings/toggle'); ?>', { method:'POST', body: fd });
      const jj = await rr.json();
      st.textContent = (jj.auto_map_orphans === '1') ? '(enabled)' : '(disabled)';
    });
  } catch(e) { console.error(e); }
})();
</script>

</body>
</html>
