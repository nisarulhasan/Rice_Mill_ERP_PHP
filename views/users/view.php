<?php
$pageTitle = 'View User';
if (!hasPermission('user_read')) { http_response_code(403); include __DIR__ . '/../errors/403.php'; return; }
$db = Database::connection();
$id = (int)($_GET['id'] ?? 0);
if (!$id && isset($segments[2])) { $id = (int)$segments[2]; }

$st = $db->prepare('SELECT u.*, r.name AS role_name, r.description AS role_description FROM users u LEFT JOIN roles r ON r.id=u.role_id WHERE u.id=? LIMIT 1');
$st->execute([$id]);
$user = $st->fetch();
if (!$user) { flashMessage('warning', 'User not found.'); redirect('users/index'); }

$ps = $db->prepare('SELECT p.module,p.action,p.slug FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=? ORDER BY p.module,p.action');
$ps->execute([$user['role_id']]);
$permissions = $ps->fetchAll();
?>
<div class="container py-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>User Details</h3>
        <div>
            <?php if (hasPermission('user_update')): ?><a class="btn btn-warning" href="<?= sanitize(url('users/edit/'.$user['id'])) ?>"><i class="fa fa-edit"></i> Edit</a><?php endif; ?>
            <a class="btn btn-secondary" href="<?= sanitize(url('users/index')) ?>">Back</a>
        </div>
    </div>

    <div class="card mb-3"><div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>Name:</strong><br><?= sanitize($user['name']) ?></div>
            <div class="col-md-4"><strong>Email:</strong><br><?= sanitize($user['email']) ?></div>
            <div class="col-md-4"><strong>Phone:</strong><br><?= sanitize($user['phone']) ?></div>
            <div class="col-md-4"><strong>Role:</strong><br><?= sanitize($user['role_name']) ?></div>
            <div class="col-md-4"><strong>Status:</strong><br><?= (int)$user['is_active']?'Active':'Inactive' ?></div>
            <div class="col-md-4"><strong>Last Login:</strong><br><?= $user['last_login'] ? sanitize(formatDate($user['last_login'],'d-m-Y h:i A')) : '-' ?></div>
        </div>
    </div></div>

    <div class="card">
        <div class="card-header">Role Permissions</div>
        <div class="card-body">
            <?php if (!$permissions): ?>
                <p class="text-muted mb-0">No permissions assigned to this role.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light"><tr><th>Module</th><th>Action</th><th>Slug</th></tr></thead>
                        <tbody><?php foreach($permissions as $p): ?><tr><td><?= sanitize($p['module']) ?></td><td><?= sanitize(ucfirst($p['action'])) ?></td><td><code><?= sanitize($p['slug']) ?></code></td></tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
