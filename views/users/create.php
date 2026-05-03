<?php
$pageTitle = 'Create User';
if (!hasPermission('user_create')) { http_response_code(403); include __DIR__ . '/../errors/403.php'; return; }
$db = Database::connection();
$roles = $db->query('SELECT id, name FROM roles WHERE is_active=1 ORDER BY name')->fetchAll();
$errors = [];
$old = ['name'=>'','email'=>'','phone'=>'','role_id'=>'','is_active'=>1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = strtolower(trim($_POST['email'] ?? ''));
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['role_id'] = (int)($_POST['role_id'] ?? 0);
    $old['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if ($old['name'] === '') $errors[] = 'Name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($old['role_id'] <= 0) $errors[] = 'Role is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';

    $c = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $c->execute([$old['email']]);
    if ($c->fetch()) $errors[] = 'Email already exists.';

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('INSERT INTO users (name,email,phone,password,role_id,is_active,created_by,created_at) VALUES (?,?,?,?,?,?,?,NOW())');
        $stmt->execute([$old['name'],$old['email'],$old['phone'],$hash,$old['role_id'],$old['is_active'],getCurrentUser()['id'] ?? null]);
        $id = (int)$db->lastInsertId();
        auditLog('create','users',$id,[],['name'=>$old['name'],'email'=>$old['email']]);
        flashMessage('success', 'User created successfully.');
        redirect('users/index');
    }
}
?>
<div class="container py-4">
    <h3>Create User</h3>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger"><?= sanitize($e) ?></div><?php endforeach; ?>
    <form method="post" class="card card-body">
        <?= csrfField() ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= sanitize($old['name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?= sanitize($old['email']) ?>" required></div>
            <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= sanitize($old['phone']) ?>"></div>
            <div class="col-md-4"><label class="form-label">Role</label><select name="role_id" class="form-select select2" required><option value="">Select Role</option><?php foreach($roles as $r): ?><option value="<?= (int)$r['id'] ?>" <?= (int)$old['role_id']===(int)$r['id']?'selected':'' ?>><?= sanitize($r['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label">Password</label><input name="password" type="password" class="form-control" required></div>
            <div class="col-12 form-check ms-2"><input class="form-check-input" type="checkbox" name="is_active" id="active" <?= (int)$old['is_active'] ? 'checked' : '' ?>><label class="form-check-label" for="active">Active</label></div>
        </div>
        <div class="mt-3"><button class="btn btn-success">Save User</button> <a class="btn btn-secondary" href="<?= sanitize(url('users/index')) ?>">Cancel</a></div>
    </form>
</div>
