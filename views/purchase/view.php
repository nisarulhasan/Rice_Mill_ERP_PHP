<?php
$pageTitle='Purchase View';
if(!hasPermission('purchase_read')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
$id=(int)($_GET['id']??0); if(!$id && isset($segments[2])) $id=(int)$segments[2];
$h=$db->prepare('SELECT p.*,v.company_name,v.gstin,w.name warehouse_name FROM purchase_invoices p LEFT JOIN vendors v ON v.id=p.vendor_id LEFT JOIN warehouses w ON w.id=p.warehouse_id WHERE p.id=?'); $h->execute([$id]); $inv=$h->fetch();
if(!$inv){ flashMessage('warning','Purchase not found'); redirect('purchase/index'); }
$it=$db->prepare('SELECT pi.*, pv.name paddy_name FROM purchase_items pi LEFT JOIN paddy_varieties pv ON pv.id=pi.paddy_id WHERE pi.purchase_id=?'); $it->execute([$id]); $items=$it->fetchAll();
?>
<div class="container py-4"><div class="d-flex justify-content-between"><h3>Purchase <?= sanitize($inv['invoice_no']) ?></h3><div><a class="btn btn-secondary" href="<?= sanitize(url('purchase/index')) ?>">Back</a> <a class="btn btn-primary" href="<?= sanitize(url('purchase/print/'.$id)) ?>">Print</a></div></div>
<div class="card my-3"><div class="card-body"><div class="row"><div class="col-md-4"><b>Vendor:</b> <?= sanitize($inv['company_name']) ?></div><div class="col-md-4"><b>Date:</b> <?= sanitize(formatDate($inv['invoice_date'])) ?></div><div class="col-md-4"><b>Warehouse:</b> <?= sanitize($inv['warehouse_name']) ?></div></div></div></div>
<table class="table table-bordered"><thead><tr><th>Paddy</th><th>Sacks</th><th>Gross Wt</th><th>Rate/Sack</th><th>Ded%</th><th>Net Wt</th><th>Net Amount</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><?= sanitize($i['paddy_name']) ?></td><td><?= sanitize($i['no_of_sacks']) ?></td><td><?= sanitize($i['total_weight_kg']) ?></td><td><?= sanitize($i['rate_per_sack']) ?></td><td><?= sanitize($i['deduction_percentage']) ?></td><td><?= sanitize($i['net_weight_kg']) ?></td><td><?= sanitize(formatIndianCurrency($i['net_amount'])) ?></td></tr><?php endforeach; ?></tbody></table>
<h4 class="text-end">Total: <?= sanitize(formatIndianCurrency($inv['total_amount'])) ?></h4></div>
