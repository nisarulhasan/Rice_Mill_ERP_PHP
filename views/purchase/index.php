<?php
$pageTitle='Purchase Register';
if(!hasPermission('purchase_read')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
$rows=$db->query('SELECT p.*, v.company_name FROM purchase_invoices p LEFT JOIN vendors v ON v.id=p.vendor_id ORDER BY p.id DESC')->fetchAll();
$flash=getFlashMessage();
?>
<div class="container py-4"><div class="d-flex justify-content-between mb-3"><h3>Purchase Register</h3><?php if(hasPermission('purchase_create')): ?><a class="btn btn-success" href="<?= sanitize(url('purchase/create')) ?>">Create Purchase</a><?php endif; ?></div>
<?php if($flash): ?><div class="alert alert-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div><?php endif; ?>
<div class="card"><div class="card-body table-responsive"><table id="purchaseTable" class="table table-striped"><thead class="table-light"><tr><th>Invoice#</th><th>Date</th><th>Vendor</th><th>Truck</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= sanitize($r['invoice_no']) ?></td><td><?= sanitize(formatDate($r['invoice_date'])) ?></td><td><?= sanitize($r['company_name']) ?></td><td><?= sanitize($r['truck_no']) ?></td><td><?= sanitize(formatIndianCurrency($r['total_amount'])) ?></td><td><span class="badge bg-<?= $r['status']==='final'?'success':'warning' ?>"><?= sanitize(ucfirst($r['status'])) ?></span></td><td><a class="btn btn-sm btn-outline-primary" href="<?= sanitize(url('purchase/view/'.$r['id'])) ?>">View</a> <a class="btn btn-sm btn-outline-secondary" href="<?= sanitize(url('purchase/print/'.$r['id'])) ?>">Print</a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jQuery&&$.fn.DataTable){$('#purchaseTable').DataTable();}});</script>
