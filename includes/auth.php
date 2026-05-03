<?php
function checkAuth(): bool {
    if (empty($_SESSION['user'])) return false;
    if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) { session_unset(); session_destroy(); return false; }
    $_SESSION['last_activity'] = time(); return true;
}
function attemptLogin($email, $password): array {
    $db = Database::connection();
    $st = $db->prepare('SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON r.id=u.role_id WHERE email=? LIMIT 1');
    $st->execute([$email]); $u=$st->fetch();
    if(!$u) return ['ok'=>false,'message'=>'Invalid credentials'];
    if(!$u['is_active']) return ['ok'=>false,'message'=>'Inactive account'];
    if($u['locked_until'] && strtotime($u['locked_until'])>time()) return ['ok'=>false,'message'=>'Account locked'];
    if(!password_verify($password,$u['password'])){
        $attempts=(int)$u['login_attempts']+1; $lock=$attempts>=MAX_LOGIN_ATTEMPTS?date('Y-m-d H:i:s',strtotime('+'.LOCKOUT_MINUTES.' minutes')):null;
        $db->prepare('UPDATE users SET login_attempts=?, locked_until=? WHERE id=?')->execute([$attempts,$lock,$u['id']]);
        return ['ok'=>false,'message'=>'Invalid credentials'];
    }
    session_regenerate_id(true);
    $db->prepare('UPDATE users SET login_attempts=0, locked_until=NULL, last_login=NOW() WHERE id=?')->execute([$u['id']]);
    $ps=$db->prepare('SELECT p.slug FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=?');$ps->execute([$u['role_id']]);
    $_SESSION['user']=$u; $_SESSION['permissions']=array_column($ps->fetchAll(),'slug'); $_SESSION['last_activity']=time();
    return ['ok'=>true,'message'=>'Login success'];
}
