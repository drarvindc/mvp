<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Visits Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:16px;}
.container{max-width:1000px;margin:0 auto;}
.row{display:flex;flex-wrap:wrap;gap:16px;}
input[type=text], input[type=date]{padding:8px;border:1px solid #ccc;border-radius:6px;}
button{padding:8px 12px;border:1px solid #333;background:#fff;border-radius:6px;cursor:pointer;}
button.primary{background:#111;color:#fff;border-color:#111;}
.pills{display:flex;gap:8px;margin:12px 0;flex-wrap:wrap;}
.pill{padding:6px 10px;border:1px solid #999;border-radius:999px;text-decoration:none;color:#111;}
.pill.active{background:#111;color:#fff;border-color:#111;}
.card{border:1px solid #e3e3e3;border-radius:8px;padding:12px;margin:10px 0;}
.small{color:#666;font-size:12px;}
.table{width:100%;border-collapse:collapse;margin-top:8px;}
.table th,.table td{border:1px solid #eee;padding:8px;text-align:left;}
.badge{padding:2px 6px;border-radius:4px;background:#f3f3f3;}
</style>
</head>
<body>
<div class="container">
  <h2>Visits Dashboard</h2>

  <form method="get" action="" class="row">
    <div>
      <label>UID</label><br>
      <input type="text" name="uid" value="<?= esc($uid ?? '') ?>" placeholder="e.g. 250001">
    </div>
    <div>
      <label>Date</label><br>
      <input type="date" name="date" value="<?= esc($date ?? date('Y-m-d')) ?>">
    </div>
    <div style="align-self:end;">
      <button type="submit" class="primary">Open</button>
    </div>
  </form>

  <?php if (isset($error)): ?>
    <div class="card" style="border-color:#f99;background:#fff6f6;"><?= esc($error) ?></div>
  <?php endif; ?>

  <?php if ($patient): ?>
    <div class="card">
      <div><strong>Patient:</strong> <?= esc($patient['name'] ?? $patient['uid']) ?> <span class="small">(UID <?= esc($patient['uid']) ?>)</span></div>
      <div class="small">Date: <?= esc($date) ?></div>

      <?php if (empty($visits)): ?>
        <div class="small">No visits for this date.</div>
      <?php else: ?>
        <div class="pills">
          <?php foreach ($visits as $v): 
                $s = (int)$v['sequence'];
                $isActive = ($active == $s);
                $label = "Visit #{$s}" . ($s==1?' (AM)':' (PM)');
                $link = current_url().'?uid='.urlencode($patient['uid']).'&date='.urlencode($date).'&seq='.$s;
          ?>
            <a class="pill <?= $isActive ? 'active' : '' ?>" href="<?= esc($link) ?>"><?= esc($label) ?></a>
          <?php endforeach; ?>
        </div>

        <?php foreach ($visits as $v):
              $s = (int)$v['sequence'];
              if ($s !== (int)$active) continue;
        ?>
          <div class="card">
            <div><strong>Showing:</strong> Visit #<?= esc($s) ?> <span class="small">(<?= esc($v['date']) ?>)</span></div>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>Filename</th>
                  <th>Filesize</th>
                  <th>Created</th>
                  <th>Open</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($v['attachments'] as $a): 
                      $url = site_url('admin/visit/file?id='.$a['id']);
                ?>
                <tr>
                  <td><?= esc($a['id']) ?></td>
                  <td><span class="badge"><?= esc($a['type']) ?></span></td>
                  <td><?= esc($a['filename']) ?></td>
                  <td><?= esc($a['filesize']) ?></td>
                  <td><?= esc($a['created_at']) ?></td>
                  <td><a href="<?= esc($url) ?>" target="_blank">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($v['attachments'])): ?>
                <tr><td colspan="6" class="small">No attachments yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
