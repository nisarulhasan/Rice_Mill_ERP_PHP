<?php
$pageTitle='Role Permissions';
if (!hasPermission('role_update')) { http_response_code(403); include __DIR__ . '/../errors/403.php'; return; }
$db=Database::connection();
$roleId=(int)($_GET['id']??($_POST['role_id']??0)); if(!$roleId && isset($segments[2])) $roleId=(int)$segments[2];
$r=$db->prepare('SELECT * FROM roles WHERE id=? LIMIT 1'); $r->execute([$roleId]); $role=$r->fetch();
if(!$role){ flashMessage('warning','Role not found.'); redirect('roles/index'); }

if($_SERVER['REQUEST_METHOD']==='POST'){
  $selected = isset($_POST['permissions']) && is_array($_POST['permissions']) ? array_map('intval', $_POST['permissions']) : [];
  $db->beginTransaction();
  try {
    $db->prepare('DELETE FROM role_permissions WHERE role_id=?')->execute([$roleId]);
    if ($selected) {
      $ins = $db->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?,?)');
      foreach($selected as $pid){ $ins->execute([$roleId,$pid]); }
    }
    $db->commit();
    auditLog('update','role_permissions',$roleId,[],['permissions'=>$selected]);
    flashMessage('success','Permissions updated successfully.');
    redirect('roles/index');
  } catch (Throwable $e) {
    if($db->inTransaction()) $db->rollBack();
    flashMessage('danger','Failed to update permissions.');
  }
}
$permissions=$db->query('SELECT * FROM permissions ORDER BY module, action')->fetchAll();
$assignedStmt=$db->prepare('SELECT permission_id FROM role_permissions WHERE role_id=?'); $assignedStmt->execute([$roleId]);
$assigned=array_map('intval', array_column($assignedStmt->fetchAll(),'permission_id'));
$grouped=[]; foreach($permissions as $p){ $grouped[$p['module']][]=$p; }
$actions=['read'=>'Read','create'=>'Create','update'=>'Edit','delete'=>'Delete','export'=>'Export','adjust'=>'Adjust','manage'=>'Manage'];
?>
<div class="container py-4">
  <h3>Permissions for: <?= sanitize($role['name']) ?></h3>
  <form method="post" class="card card-body">
    <?= csrfField() ?><input type="hidden" name="role_id" value="<?= (int)$roleId ?>">
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light"><tr><th>Module</th><th>Read</th><th>Create</th><th>Edit</th><th>Delete</th><th>Export</th><th>Other</th></tr></thead>
        <tbody>
        <?php foreach($grouped as $module=>$rows):
            $map=[]; foreach($rows as $x){ $map[strtolower($x['action'])]=$x; }
        ?>
          <tr>
            <td><?= sanitize($module) ?></td>
            <?php foreach(['read','create','update','delete','export'] as $ac): ?>
              <td><?php if(isset($map[$ac])): $pid=(int)$map[$ac]['id']; ?><input type="checkbox" name="permissions[]" value="<?= $pid ?>" <?= in_array($pid,$assigned,true)?'checked':'' ?>><?php else: ?>-<?php endif; ?></td>
            <?php endforeach; ?>
            <td>
              <?php foreach($rows as $rw): $a=strtolower($rw['action']); if(in_array($a,['read','create','update','delete','export'],true)) continue; $pid=(int)$rw['id']; ?>
                <label class="me-2"><input type="checkbox" name="permissions[]" value="<?= $pid ?>" <?= in_array($pid,$assigned,true)?'checked':'' ?>> <?= sanitize($actions[$a] ?? ucfirst($rw['action'])) ?></label>
              <?php endforeach; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div><button class="btn btn-primary">Save Permissions</button> <a class="btn btn-secondary" href="<?= sanitize(url('roles/index')) ?>">Back</a></div>
  </form>
</div>
