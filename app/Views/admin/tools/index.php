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
        </ul>
    </div>
</body>
</html>
