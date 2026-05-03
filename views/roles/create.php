<?php
$pageTitle = 'Create Role';
if (!hasPermission('role_create')) { http_response_code(403); include __DIR__ . '/../errors/403.php'; return; }
$db = Database::connection();
$errors=[]; $old=['name'=>'','description'=>'','is_active'=>1];
if($_SERVER['REQUEST_METHOD']==='POST'){
  $old['name']=trim($_POST['name']??'');
  $old['description']=trim($_POST['description']??'');
  $old['is_active']=isset($_POST['is_active'])?1:0;
  if($old['name']==='') $errors[]='Role name is required.';
  $c=$db->prepare('SELECT id FROM roles WHERE name=? LIMIT 1'); $c->execute([$old['name']]); if($c->fetch()) $errors[]='Role name already exists.';
  if(!$errors){
    $st=$db->prepare('INSERT INTO roles (name,description,is_active,created_at) VALUES (?,?,?,NOW())');
    $st->execute([$old['name'],$old['description'],$old['is_active']]);
    $id=(int)$db->lastInsertId();
    auditLog('create','roles',$id,[],['name'=>$old['name']]);
    flashMessage('success','Role created successfully.'); redirect('roles/index');
  }
}
?>
<div class="container py-4"><h3>Create Role</h3>
<?php foreach($errors as $e): ?><div class="alert alert-danger"><?= sanitize($e) ?></div><?php endforeach; ?>
<form method="post" class="card card-body"><?= csrfField() ?>
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Role Name</label><input class="form-control" name="name" value="<?= sanitize($old['name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Description</label><input class="form-control" name="description" value="<?= sanitize($old['description']) ?>"></div>
<div class="col-12 form-check ms-2"><input class="form-check-input" type="checkbox" name="is_active" id="active" <?= (int)$old['is_active']?'checked':'' ?>><label class="form-check-label" for="active">Active</label></div>
</div>
<div class="mt-3"><button class="btn btn-success">Save Role</button> <a class="btn btn-secondary" href="<?= sanitize(url('roles/index')) ?>">Cancel</a></div>
</form></div>
