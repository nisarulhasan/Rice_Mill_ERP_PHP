<?php
$pageTitle='Vendors';
if(!hasPermission('vendor_read')){http_response_code(403); include __DIR__.'/../../errors/403.php'; return;}
$db=Database::connection();
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_id'])){
  if(!hasPermission('vendor_delete')){ flashMessage('danger','No permission to delete vendors.'); redirect('masters/vendors/index'); }
  $id=(int)$_POST['delete_id'];
  $q=$db->prepare('SELECT * FROM vendors WHERE id=? LIMIT 1'); $q->execute([$id]); $old=$q->fetch();
  if($old){ $db->prepare('DELETE FROM vendors WHERE id=?')->execute([$id]); auditLog('delete','vendors',$id,$old,[]); flashMessage('success','Vendor deleted.'); }
  else flashMessage('warning','Vendor not found.');
  redirect('masters/vendors/index');
}
$vendors=$db->query('SELECT * FROM vendors ORDER BY id DESC')->fetchAll(); $flash=getFlashMessage();
?>
<div class="container py-4">
<div class="d-flex justify-content-between mb-3"><h3>Vendors</h3><?php if(hasPermission('vendor_create')): ?><a class="btn btn-success" href="<?= sanitize(url('masters/vendors/create')) ?>">Add Vendor</a><?php endif; ?></div>
<?php if($flash): ?><div class="alert alert-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div><?php endif; ?>
<div class="card"><div class="card-body table-responsive"><table id="vendorsTable" class="table table-striped align-middle"><thead class="table-light"><tr><th>Company</th><th>Contact</th><th>GSTIN</th><th>Phone</th><th>Opening Balance</th><th>Credit Days</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($vendors as $v): ?><tr>
<td><?= sanitize($v['company_name']) ?></td><td><?= sanitize($v['contact_person']) ?></td><td><?= sanitize($v['gstin']) ?></td><td><?= sanitize($v['phone']) ?></td>
<td><?= sanitize(strtoupper($v['balance_type'])) ?> <?= sanitize(formatIndianCurrency($v['opening_balance'])) ?></td><td><?= (int)$v['credit_period_days'] ?></td>
<td><span class="badge <?= (int)$v['is_active']?'bg-success':'bg-secondary' ?>"><?= (int)$v['is_active']?'Active':'Inactive' ?></span></td>
<td>
<a class="btn btn-sm btn-outline-info" href="<?= sanitize(url('masters/vendors/ledger/'.$v['id'])) ?>"><i class="fa fa-book"></i></a>
<?php if(hasPermission('vendor_update')): ?><a class="btn btn-sm btn-outline-warning" href="<?= sanitize(url('masters/vendors/edit/'.$v['id'])) ?>"><i class="fa fa-edit"></i></a><?php endif; ?>
<?php if(hasPermission('vendor_delete')): ?><form method="post" class="d-inline" onsubmit="return confirm('Delete vendor?');"><?= csrfField() ?><input type="hidden" name="delete_id" value="<?= (int)$v['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button></form><?php endif; ?>
</td></tr><?php endforeach; ?>
</tbody></table></div></div></div>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jQuery&&$.fn.DataTable){$('#vendorsTable').DataTable();}});</script>
