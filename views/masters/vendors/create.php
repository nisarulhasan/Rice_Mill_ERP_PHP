<?php
$pageTitle='Create Vendor';
if(!hasPermission('vendor_create')){http_response_code(403); include __DIR__.'/../../errors/403.php'; return;}
$db=Database::connection();
$errors=[]; $old=['company_name'=>'','contact_person'=>'','gstin'=>'','pan'=>'','address'=>'','city'=>'','state'=>'','pincode'=>'','phone'=>'','email'=>'','opening_balance'=>'0.00','balance_type'=>'cr','credit_period_days'=>'0','bank_name'=>'','bank_account_no'=>'','bank_ifsc'=>'','is_active'=>1];
if($_SERVER['REQUEST_METHOD']==='POST'){
 foreach($old as $k=>$v){ if($k==='is_active') continue; $old[$k]=trim($_POST[$k] ?? $v); }
 $old['is_active']=isset($_POST['is_active'])?1:0;
 if($old['company_name']==='') $errors[]='Company name is required.';
 if($old['contact_person']==='') $errors[]='Contact person is required.';
 if($old['phone']==='') $errors[]='Phone is required.';
 if($old['gstin']!=='' && !preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',$old['gstin'])) $errors[]='Invalid GSTIN format.';
 if($old['email']!=='' && !filter_var($old['email'],FILTER_VALIDATE_EMAIL)) $errors[]='Invalid email.';
 if(!$errors){
  $st=$db->prepare('INSERT INTO vendors (company_name,contact_person,gstin,pan,address,city,state,pincode,phone,email,opening_balance,balance_type,credit_period_days,bank_name,bank_account_no,bank_ifsc,is_active,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
  $st->execute([$old['company_name'],$old['contact_person'],strtoupper($old['gstin']),strtoupper($old['pan']),$old['address'],$old['city'],$old['state'],$old['pincode'],$old['phone'],$old['email'],(float)$old['opening_balance'],$old['balance_type'],(int)$old['credit_period_days'],$old['bank_name'],$old['bank_account_no'],strtoupper($old['bank_ifsc']),$old['is_active']]);
  $id=(int)$db->lastInsertId();
  auditLog('create','vendors',$id,[],['company_name'=>$old['company_name'],'opening_balance'=>$old['opening_balance'],'balance_type'=>$old['balance_type']]);
  flashMessage('success','Vendor created successfully.'); redirect('masters/vendors/index');
 }
}
?>
<div class="container py-4"><h3>Create Vendor</h3><?php foreach($errors as $e): ?><div class="alert alert-danger"><?= sanitize($e) ?></div><?php endforeach; ?>
<form method="post" class="card card-body"><?= csrfField() ?>
<div class="row g-3">
<?php foreach(['company_name'=>'Company Name','contact_person'=>'Contact Person','phone'=>'Phone','email'=>'Email','gstin'=>'GSTIN','pan'=>'PAN','city'=>'City','state'=>'State','pincode'=>'Pincode','credit_period_days'=>'Credit Days','bank_name'=>'Bank Name','bank_account_no'=>'Bank Account','bank_ifsc'=>'IFSC'] as $k=>$label): ?>
<div class="col-md-4"><label class="form-label"><?= $label ?></label><input name="<?= $k ?>" class="form-control" value="<?= sanitize($old[$k]) ?>"></div><?php endforeach; ?>
<div class="col-md-12"><label class="form-label">Address</label><textarea name="address" class="form-control"><?= sanitize($old['address']) ?></textarea></div>
<div class="col-md-4"><label class="form-label">Opening Balance</label><input type="number" step="0.01" name="opening_balance" class="form-control" value="<?= sanitize($old['opening_balance']) ?>"></div>
<div class="col-md-4"><label class="form-label">Balance Type</label><select name="balance_type" class="form-select"><option value="cr" <?= $old['balance_type']==='cr'?'selected':'' ?>>Cr</option><option value="dr" <?= $old['balance_type']==='dr'?'selected':'' ?>>Dr</option></select></div>
<div class="col-md-4 form-check mt-5 ms-2"><input class="form-check-input" type="checkbox" name="is_active" <?= (int)$old['is_active']?'checked':'' ?>><label class="form-check-label">Active</label></div>
</div><div class="mt-3"><button class="btn btn-success">Save</button> <a class="btn btn-secondary" href="<?= sanitize(url('masters/vendors/index')) ?>">Cancel</a></div>
</form></div>
