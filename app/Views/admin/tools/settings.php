<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Settings</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;padding:16px;line-height:1.45}
    .wrap{max-width:820px;margin:0 auto}
    .card{border:1px solid #ddd;border-radius:8px;padding:16px;margin-bottom:16px}
    h2{margin:0 0 12px}
    label{font-weight:600}
    .row{display:grid;grid-template-columns:240px 1fr;gap:10px;align-items:center;margin:10px 0}
    input[type=checkbox]{transform:scale(1.2);margin-right:8px}
    select,input[type=text]{font-size:15px;padding:8px;border:1px solid #bbb;border-radius:6px;width:100%}
    .actions{margin-top:12px}
    button{background:#2563eb;color:#fff;border:0;padding:10px 14px;border-radius:6px;cursor:pointer}
    .muted{color:#666}
    .msg{background:#ecfdf5;border:1px solid #34d399;color:#065f46;padding:8px 10px;border-radius:6px;margin-bottom:12px}
    code{background:#f6f8fa;padding:2px 4px;border-radius:4px}
  </style>
</head>
<body>
<div class="wrap">
  <h2>Admin Settings</h2>

  <?php if (session()->getFlashdata('msg')): ?>
    <div class="msg"><?= esc(session()->getFlashdata('msg')) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= site_url('admin/tools/settings-save') ?>">
    <?= csrf_field() ?>
    <div class="card">
      <div class="row">
        <label>Auto‑map orphan docs on upload</label>
        <div><label><input type="checkbox" name="auto_map_orphans" <?= $auto_map_orphans ? 'checked' : '' ?>> Enable</label></div>
      </div>

      <div class="row">
        <label>Date display format</label>
        <div>
          <select name="date_display_format">
            <option value="dd-mm-yyyy" <?= $date_display_format==='dd-mm-yyyy'?'selected':'' ?>>dd‑mm‑yyyy</option>
            <option value="yyyy-mm-dd" <?= $date_display_format==='yyyy-mm-dd'?'selected':'' ?>>yyyy‑mm‑dd</option>
          </select>
          <div class="muted">Used by new views; existing pages continue to accept both.</div>
        </div>
      </div>

      <div class="row">
        <label>Public access: Visits Lite</label>
        <div><label><input type="checkbox" name="visits_lite_public" <?= $visits_lite_public ? 'checked' : '' ?>> Allow basic read‑only list</label></div>
      </div>
    </div>

    <div class="actions">
      <button type="submit">Save Settings</button>
    </div>
  </form>

  <div class="card">
    <h3 style="margin-top:0">Environment (read‑only)</h3>
    <div class="row">
      <label>ANDROID_API_TOKEN</label>
      <div><code><?= esc($android_token) ?></code></div>
    </div>
    <div class="row">
      <label>DEV_NO_AUTH</label>
      <div><code><?= esc($dev_no_auth) ?></code></div>
    </div>
    <div class="muted">These come from <code>.env</code> and are shown for reference only.</div>
  </div>

  <div class="card">
    <h3 style="margin-top:0">JSON endpoints</h3>
    <div class="muted">
      GET <code>/index.php/admin/tools/settings</code> → current values<br>
      POST <code>/index.php/admin/tools/settings/toggle</code> (<code>enable=1|0</code>) → legacy toggle for auto‑map
    </div>
  </div>
</div>
</body>
</html>
