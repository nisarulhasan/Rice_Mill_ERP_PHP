<?php
$pageTitle = 'Roles';
if (!hasPermission('role_read')) { http_response_code(403); include __DIR__ . '/../errors/403.php'; return; }
$db = Database::connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!hasPermission('role_delete')) { flashMessage('danger', 'No permission to delete roles.'); redirect('roles/index'); }
    $id = (int)$_POST['delete_id'];
    $r = $db->prepare('SELECT * FROM roles WHERE id=? LIMIT 1'); $r->execute([$id]); $old=$r->fetch();
    if ($old && strtolower((string)$old['name']) !== 'admin') {
        $u = $db->prepare('SELECT COUNT(*) c FROM users WHERE role_id=?'); $u->execute([$id]); $count=(int)$u->fetch()['c'];
        if ($count > 0) {
            flashMessage('warning', 'Cannot delete role assigned to users.');
        } else {
            $db->prepare('DELETE FROM role_permissions WHERE role_id=?')->execute([$id]);
            $db->prepare('DELETE FROM roles WHERE id=?')->execute([$id]);
            auditLog('delete', 'roles', $id, $old, []);
            flashMessage('success', 'Role deleted successfully.');
        }
    } else {
        flashMessage('warning', 'Role not found or protected role.');
    }
    redirect('roles/index');
}

$roles = $db->query('SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.role_id=r.id) AS user_count FROM roles r ORDER BY r.id DESC')->fetchAll();
$flash = getFlashMessage();
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Role Management</h3>
    <?php if (hasPermission('role_create')): ?><a class="btn btn-success" href="<?= sanitize(url('roles/create')) ?>"><i class="fa fa-plus"></i> Add Role</a><?php endif; ?>
  </div>
  <?php if ($flash): ?><div class="alert alert-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div><?php endif; ?>
  <div class="card"><div class="card-body table-responsive">
    <table id="rolesTable" class="table table-striped align-middle">
      <thead class="table-light"><tr><th>Role</th><th>Description</th><th>User Count</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($roles as $role): ?>
        <tr>
          <td><?= sanitize($role['name']) ?></td>
          <td><?= sanitize($role['description']) ?></td>
          <td><span class="badge bg-primary"><?= (int)$role['user_count'] ?></span></td>
          <td><span class="badge <?= (int)$role['is_active'] ? 'bg-success':'bg-secondary' ?>"><?= (int)$role['is_active'] ? 'Active':'Inactive' ?></span></td>
          <td>
            <?php if (hasPermission('role_update')): ?><a class="btn btn-sm btn-outline-warning" href="<?= sanitize(url('roles/edit/'.$role['id'])) ?>"><i class="fa fa-edit"></i></a><?php endif; ?>
            <?php if (hasPermission('role_update')): ?><a class="btn btn-sm btn-outline-info" href="<?= sanitize(url('roles/permissions/'.$role['id'])) ?>"><i class="fa fa-key"></i></a><?php endif; ?>
            <?php if (hasPermission('role_delete')): ?>
            <form class="d-inline" method="post" onsubmit="return confirm('Delete role?');"><?= csrfField() ?><input type="hidden" name="delete_id" value="<?= (int)$role['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button></form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jQuery&&$.fn.DataTable){$('#rolesTable').DataTable();}});</script>
