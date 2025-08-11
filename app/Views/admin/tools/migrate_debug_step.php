<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Migrate Debug Step</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, sans-serif; padding: 16px; }
    code, pre { background: #111; color: #0f0; padding: 10px; border-radius: 6px; display: block; white-space: pre-wrap; }
    table { border-collapse: collapse; width: 100%; margin-top: 1em; }
    td, th { border: 1px solid #ddd; padding: 6px; }
  </style>
</head>
<body>
  <h2>Migrate Debug Step</h2>
  <p>This will run only the <strong>next pending migration</strong> so we can see exactly which one fails.</p>

  <p><a href="?key=arvindrchauhan1723&step=1">▶ Run next pending migration (one step)</a></p>

  <?php if (!empty($ranClass)): ?>
    <h3>Run Result</h3>
    <p><?= esc($ranClass) ?></p>
    <?php if (!empty($errorMsg)): ?>
      <h4>Error</h4>
      <pre><?= esc($errorMsg) ?></pre>
    <?php endif; ?>
  <?php endif; ?>

  <h3>Migration Files</h3>
  <table>
    <tr><th>#</th><th>File</th><th>Class</th><th>Status</th></tr>
    <?php foreach ($files as $i => $f): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?= esc($f['file']) ?></td>
        <td><code><?= esc($f['class']) ?></code></td>
        <td><?= $f['ran'] ? '✅ ran' : '⏳ pending' ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
