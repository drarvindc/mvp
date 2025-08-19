<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visits Admin View</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4">Visits Admin View</h1>

        <form method="get" class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="uid" value="<?= esc($_GET['uid'] ?? '') ?>" class="form-control" placeholder="Patient UID">
            </div>
            <div class="col-md-3">
                <input type="date" name="date" value="<?= esc($_GET['date'] ?? '') ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Search</button>
            </div>
        </form>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= esc($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($visit)): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Visit #<?= esc($visit['id']) ?> (<?= esc($visit['visit_date']) ?>)</h5>
                    <p>UID: <?= esc($visit['patient_unique_id']) ?> | Sequence: <?= esc($visit['visit_seq']) ?></p>
                </div>
            </div>

            <h4>Documents</h4>
            <?php if (empty($documents)): ?>
                <p>No documents uploaded for this visit.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($documents as $doc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= esc($doc['filename']) ?>
                            <a href="<?= site_url('admin/visit/file?id='.$doc['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Open</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
