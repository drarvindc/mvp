<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <h3 class="mb-3">Admin Login</h3>
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
      <?php endif; ?>
      <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-info"><?= esc(session()->getFlashdata('message')) ?></div>
      <?php endif; ?>
      <form method="post" action="<?= site_url('admin/login') ?>">
        <input type="hidden" name="next" value="<?= esc($next) ?>">
        <div class="mb-2">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <div class="mb-2">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
          <label class="form-check-label" for="remember">Remember me (30 days)</label>
        </div>
        <button class="btn btn-primary w-100">Sign in</button>
      </form>
      <div class="text-muted small mt-3">
        Session lifetime is controlled by <code>app.sessionExpiration</code> in <code>.env</code>.
      </div>
    </div>
  </div>
</body>
</html>
