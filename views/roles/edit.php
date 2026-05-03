<?php
$pageTitle='Edit Role';
if (!hasPermission('role_update')) { http_response_code(403); include __DIR__ . '/../errors/403.php'; return; }
$db=Database::connection();
$id=(int)($_GET['id']??($_POST['id']??0)); if(!$id && isset($segments[2])) $id=(int)$segments[2];
$s=$db->prepare('SELECT * FROM roles WHERE id=? LIMIT 1'); $s->execute([$id]); $role=$s->fetch();
if(!$role){ flashMessage('warning','Role not found.'); redirect('roles/index'); }
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']??''); $description=trim($_POST['description']??''); $active=isset($_POST['is_active'])?1:0;
  if($name==='') $errors[]='Role name is required.';
  $c=$db->prepare('SELECT id FROM roles WHERE name=? AND id<>? LIMIT 1'); $c->execute([$name,$id]); if($c->fetch()) $errors[]='Role name already exists.';
  if(!$errors){
    $old=$role;
    $u=$db->prepare('UPDATE roles SET name=?,description=?,is_active=?,updated_at=NOW() WHERE id=?');
    $u->execute([$name,$description,$active,$id]);
    auditLog('update','roles',$id,$old,['name'=>$name,'description'=>$description,'is_active'=>$active]);
    flashMessage('success','Role updated successfully.'); redirect('roles/index');
  }
  $role=array_merge($role,['name'=>$name,'description'=>$description,'is_active'=>$active]);
}
?>
<div class="container py-4"><h3>Edit Role</h3>
<?php foreach($errors as $e): ?><div class="alert alert-danger"><?= sanitize($e) ?></div><?php endforeach; ?>
<form method="post" class="card card-body"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Role Name</label><input class="form-control" name="name" value="<?= sanitize($role['name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Description</label><input class="form-control" name="description" value="<?= sanitize($role['description']) ?>"></div>
<div class="col-12 form-check ms-2"><input class="form-check-input" type="checkbox" name="is_active" id="active" <?= (int)$role['is_active']?'checked':'' ?>><label class="form-check-label" for="active">Active</label></div>
</div>
<div class="mt-3"><button class="btn btn-primary">Update Role</button> <a class="btn btn-secondary" href="<?= sanitize(url('roles/index')) ?>">Cancel</a></div>
</form></div>
