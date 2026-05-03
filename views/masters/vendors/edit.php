<?php
$pageTitle='Edit Vendor';
if(!hasPermission('vendor_update')){http_response_code(403); include __DIR__.'/../../errors/403.php'; return;}
$db=Database::connection();
$id=(int)($_GET['id']??($_POST['id']??0)); if(!$id && isset($segments[3])) $id=(int)$segments[3];
$q=$db->prepare('SELECT * FROM vendors WHERE id=? LIMIT 1'); $q->execute([$id]); $vendor=$q->fetch();
if(!$vendor){ flashMessage('warning','Vendor not found.'); redirect('masters/vendors/index'); }
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  $data=[]; foreach(['company_name','contact_person','gstin','pan','address','city','state','pincode','phone','email','opening_balance','balance_type','credit_period_days','bank_name','bank_account_no','bank_ifsc'] as $f){$data[$f]=trim($_POST[$f]??'');}
  $data['is_active']=isset($_POST['is_active'])?1:0;
  if($data['company_name']==='') $errors[]='Company name is required.';
  if($data['contact_person']==='') $errors[]='Contact person is required.';
  if($data['phone']==='') $errors[]='Phone is required.';
  if($data['gstin']!=='' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',$data['gstin'])) $errors[]='Invalid GSTIN format.';
  if($data['email']!=='' && !filter_var($data['email'],FILTER_VALIDATE_EMAIL)) $errors[]='Invalid email.';
  if(!$errors){
    $old=$vendor;
    $u=$db->prepare('UPDATE vendors SET company_name=?,contact_person=?,gstin=?,pan=?,address=?,city=?,state=?,pincode=?,phone=?,email=?,opening_balance=?,balance_type=?,credit_period_days=?,bank_name=?,bank_account_no=?,bank_ifsc=?,is_active=? WHERE id=?');
    $u->execute([$data['company_name'],$data['contact_person'],strtoupper($data['gstin']),strtoupper($data['pan']),$data['address'],$data['city'],$data['state'],$data['pincode'],$data['phone'],$data['email'],(float)$data['opening_balance'],$data['balance_type'],(int)$data['credit_period_days'],$data['bank_name'],$data['bank_account_no'],strtoupper($data['bank_ifsc']),$data['is_active'],$id]);
    auditLog('update','vendors',$id,$old,$data); flashMessage('success','Vendor updated.'); redirect('masters/vendors/index');
  }
  $vendor=array_merge($vendor,$data);
}
?>
<div class="container py-4"><h3>Edit Vendor</h3><?php foreach($errors as $e): ?><div class="alert alert-danger"><?= sanitize($e) ?></div><?php endforeach; ?>
<form method="post" class="card card-body"><?= csrfField() ?><input type="hidden" name="id" value="<?= (int)$id ?>"><div class="row g-3">
<?php foreach(['company_name'=>'Company Name','contact_person'=>'Contact Person','phone'=>'Phone','email'=>'Email','gstin'=>'GSTIN','pan'=>'PAN','city'=>'City','state'=>'State','pincode'=>'Pincode','credit_period_days'=>'Credit Days','bank_name'=>'Bank Name','bank_account_no'=>'Bank Account','bank_ifsc'=>'IFSC'] as $k=>$label): ?><div class="col-md-4"><label class="form-label"><?= $label ?></label><input name="<?= $k ?>" class="form-control" value="<?= sanitize($vendor[$k]??'') ?>"></div><?php endforeach; ?>
<div class="col-md-12"><label class="form-label">Address</label><textarea name="address" class="form-control"><?= sanitize($vendor['address']??'') ?></textarea></div>
<div class="col-md-4"><label class="form-label">Opening Balance</label><input type="number" step="0.01" name="opening_balance" class="form-control" value="<?= sanitize($vendor['opening_balance']) ?>"></div>
<div class="col-md-4"><label class="form-label">Balance Type</label><select name="balance_type" class="form-select"><option value="cr" <?= ($vendor['balance_type']??'')==='cr'?'selected':'' ?>>Cr</option><option value="dr" <?= ($vendor['balance_type']??'')==='dr'?'selected':'' ?>>Dr</option></select></div>
<div class="col-md-4 form-check mt-5 ms-2"><input class="form-check-input" type="checkbox" name="is_active" <?= (int)($vendor['is_active']??0)?'checked':'' ?>><label class="form-check-label">Active</label></div>
</div><div class="mt-3"><button class="btn btn-primary">Update</button> <a class="btn btn-secondary" href="<?= sanitize(url('masters/vendors/index')) ?>">Cancel</a></div></form></div>
