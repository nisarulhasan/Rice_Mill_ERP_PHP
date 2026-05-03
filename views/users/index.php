<?php
$pageTitle = 'Users';
$db = Database::connection();
$currentUser = getCurrentUser();

if (!hasPermission('user_read')) {
    http_response_code(403);
    include __DIR__ . '/../errors/403.php';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!hasPermission('user_delete')) {
        flashMessage('danger', 'You do not have permission to delete users.');
        redirect('users/index');
    }
    $deleteId = (int)$_POST['delete_id'];
    if ($deleteId === (int)($currentUser['id'] ?? 0)) {
        flashMessage('warning', 'You cannot delete your own account.');
        redirect('users/index');
    }

    $check = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $check->execute([$deleteId]);
    $old = $check->fetch();
    if ($old) {
        $del = $db->prepare('DELETE FROM users WHERE id = ?');
        $del->execute([$deleteId]);
        auditLog('delete', 'users', $deleteId, $old, []);
        flashMessage('success', 'User deleted successfully.');
    } else {
        flashMessage('warning', 'User not found.');
    }
    redirect('users/index');
}

$users = $db->query("SELECT u.*, r.name AS role_name FROM users u LEFT JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC")->fetchAll();
$flash = getFlashMessage();
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">User Management</h3>
        <?php if (hasPermission('user_create')): ?>
            <a href="<?= sanitize(url('users/create')) ?>" class="btn btn-success"><i class="fa fa-plus"></i> Add User</a>
        <?php endif; ?>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped table-hover align-middle">
                    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= sanitize($u['name']) ?></td>
                            <td><?= sanitize($u['email']) ?></td>
                            <td><?= sanitize($u['phone']) ?></td>
                            <td><span class="badge bg-info"><?= sanitize($u['role_name'] ?? 'N/A') ?></span></td>
                            <td><span class="badge <?= (int)$u['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= (int)$u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td><?= $u['last_login'] ? sanitize(formatDate($u['last_login'], 'd-m-Y h:i A')) : '-' ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="<?= sanitize(url('users/view/' . (int)$u['id'])) ?>"><i class="fa fa-eye"></i></a>
                                <?php if (hasPermission('user_update')): ?>
                                    <a class="btn btn-sm btn-outline-warning" href="<?= sanitize(url('users/edit/' . (int)$u['id'])) ?>"><i class="fa fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if (hasPermission('user_delete')): ?>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="delete_id" value="<?= (int)$u['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fa fa-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){ if(window.jQuery && $.fn.DataTable){ $('#usersTable').DataTable(); } });
</script>
