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

  <form method="get" action="">
    <div class="row">
      <div>
        <label>UID<br><input type="text" name="uid" value="<?= esc($uid ?? '') ?>" placeholder="e.g. 250001"></label>
      </div>
      <div>
        <label>Date<br><input type="date" name="date" value="<?= esc($date ?? date('Y-m-d')) ?>"></label>
      </div>
      <div style="align-self:flex-end">
        <button class="primary" type="submit">Load</button>
      </div>
    </div>
  </form>

  <?php if (!empty($patient)): ?>
    <div class="card">
      <div><strong>Pet UID:</strong> <?= esc($patient['unique_id']) ?> &nbsp; <span class="small">Pet ID: <?= esc($patient['pet_id']) ?></span></div>
    </div>

    <?php if (empty($visits)): ?>
      <div class="card small">No visits for <?= esc($date) ?>.</div>
    <?php else: ?>
      <div class="pills">
        <?php foreach ($visits as $v): 
              $seq = (int)$v['sequence'];
              $active = ($seq === (int)$activeSeq);
              $label = 'Visit #'.$seq.($seq===1?' (AM)':' (PM)');
              $qs = http_build_query(['uid'=>$uid,'date'=>$date,'seq'=>$seq]);
        ?>
          <a class="pill <?= $active ? 'active':'' ?>" href="?<?= $qs ?>"><?= esc($label) ?></a>
        <?php endforeach; ?>
      </div>

      <?php foreach ($visits as $v): 
            $seq = (int)$v['sequence'];
            $visible = ($seq === (int)$activeSeq);
      ?>
        <div class="card" style="<?= $visible ? '' : 'display:none' ?>">
          <div><strong><?= 'Visit #'.$seq ?></strong> <span class="small">on <?= esc($v['visit_date']) ?></span></div>
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
              <?php foreach ($attachmentsByVisit[$seq] ?? [] as $a): 
                    $url = site_url('admin/visit/file?id='.$a['id']);
              ?>
              <tr>
                <td><?= esc($a['id']) ?></td>
                <td><span class="badge"><?= esc($a['type']) ?></span></td>
                <td><?= esc($a['filename']) ?></td>
                <td><?= esc(number_format((int)$a['filesize'])) ?></td>
                <td><?= esc($a['created_at']) ?></td>
                <td><a href="<?= $url ?>" target="_blank">Open</a></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($attachmentsByVisit[$seq] ?? [])): ?>
              <tr><td colspan="6" class="small">No attachments yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>
