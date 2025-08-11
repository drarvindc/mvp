<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Migrate Debug</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial,sans-serif;padding:16px}
  code,pre{background:#111;color:#0f0;padding:10px;border-radius:6px;display:block;white-space:pre-wrap}
  table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:6px}</style>
</head>
<body>
  <h2>Migrate Debug</h2>
  <p>This page lists discovered migrations and lets you run the pending ones once to capture the exact failure.</p>

  <p><a href="?key=arvindrchauhan1723&step=1">▶ Run pending migrations (once)</a></p>

  <?php if (!empty($ran)): ?>
    <h3>Run Result</h3>
    <p><?= esc($ran) ?></p>
    <?php if (!empty($error)): ?>
      <h4>Error</h4>
      <pre><?= esc($error) ?></pre>
      <p>Copy the class/file below that appears just before the failure; that’s the culprit.</p>
    <?php endif; ?>
  <?php endif; ?>

  <h3>Discovered migration files</h3>
  <table>
    <tr><th>#</th><th>File</th><th>Class</th></tr>
    <?php foreach ($files as $i => $f): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?= esc($f['file']) ?></td>
        <td><code><?= esc($f['class']) ?></code></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
