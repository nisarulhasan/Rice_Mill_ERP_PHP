<?php
$pageTitle = 'Edit User';
if (!hasPermission('user_update')) { http_response_code(403); include __DIR__ . '/../errors/403.php'; return; }
$db = Database::connection();
$id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
if (!$id && isset($segments[2])) { $id = (int)$segments[2]; }
$roles = $db->query('SELECT id, name FROM roles WHERE is_active=1 ORDER BY name')->fetchAll();
$q = $db->prepare('SELECT * FROM users WHERE id=? LIMIT 1'); $q->execute([$id]); $user = $q->fetch();
if (!$user) { flashMessage('warning','User not found.'); redirect('users/index'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';
    if ($name==='') $errors[]='Name is required.';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Valid email is required.';
    if ($roleId<=0) $errors[]='Role is required.';
    $c = $db->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1'); $c->execute([$email,$id]); if ($c->fetch()) $errors[]='Email already exists.';
    if (!$errors) {
        $old = $user;
        if ($password !== '') {
            if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
            else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $st = $db->prepare('UPDATE users SET name=?, email=?, phone=?, role_id=?, is_active=?, password=?, updated_at=NOW() WHERE id=?');
                $st->execute([$name,$email,$phone,$roleId,$isActive,$hash,$id]);
            }
        } else {
            $st = $db->prepare('UPDATE users SET name=?, email=?, phone=?, role_id=?, is_active=?, updated_at=NOW() WHERE id=?');
            $st->execute([$name,$email,$phone,$roleId,$isActive,$id]);
        }
        if (!$errors) {
            auditLog('update','users',$id,$old,['name'=>$name,'email'=>$email,'phone'=>$phone,'role_id'=>$roleId,'is_active'=>$isActive]);
            flashMessage('success','User updated successfully.');
            redirect('users/index');
        }
    }
    $user = array_merge($user,['name'=>$name,'email'=>$email,'phone'=>$phone,'role_id'=>$roleId,'is_active'=>$isActive]);
}
?>
<div class="container py-4"><h3>Edit User</h3>
<?php foreach($errors as $e): ?><div class="alert alert-danger"><?= sanitize($e) ?></div><?php endforeach; ?>
<form method="post" class="card card-body">
<?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= sanitize($user['name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= sanitize($user['email']) ?>" required></div>
<div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= sanitize($user['phone']) ?>"></div>
<div class="col-md-4"><label class="form-label">Role</label><select name="role_id" class="form-select select2" required><?php foreach($roles as $r): ?><option value="<?= (int)$r['id'] ?>" <?= (int)$user['role_id']===(int)$r['id']?'selected':'' ?>><?= sanitize($r['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">New Password (optional)</label><input type="password" name="password" class="form-control"></div>
<div class="col-12 form-check ms-2"><input class="form-check-input" type="checkbox" name="is_active" id="active" <?= (int)$user['is_active'] ? 'checked' : '' ?>><label class="form-check-label" for="active">Active</label></div>
</div>
<div class="mt-3"><button class="btn btn-primary">Update User</button> <a class="btn btn-secondary" href="<?= sanitize(url('users/index')) ?>">Cancel</a></div>
</form></div>
