<?php
if(!hasPermission('purchase_read')){http_response_code(403); exit('Forbidden');}
$db=Database::connection();
$id=(int)($_GET['id']??0); if(!$id && isset($segments[2])) $id=(int)$segments[2];
$h=$db->prepare('SELECT p.*,v.company_name,v.address,v.gstin FROM purchase_invoices p LEFT JOIN vendors v ON v.id=p.vendor_id WHERE p.id=?'); $h->execute([$id]); $inv=$h->fetch();
$it=$db->prepare('SELECT pi.*, pv.name paddy_name FROM purchase_items pi LEFT JOIN paddy_varieties pv ON pv.id=pi.paddy_id WHERE pi.purchase_id=?'); $it->execute([$id]); $items=$it->fetchAll();
?><!doctype html><html><head><title>Print Purchase</title><style>body{font-family:Arial}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:6px} .text-end{text-align:right} @media print {.no-print{display:none}}</style></head><body>
<button class="no-print" onclick="window.print()">Print</button>
<h2>Purchase Invoice</h2><p><b>Invoice:</b> <?= sanitize($inv['invoice_no']??'') ?> | <b>Date:</b> <?= sanitize(isset($inv['invoice_date'])?formatDate($inv['invoice_date']):'') ?></p>
<p><b>Vendor:</b> <?= sanitize($inv['company_name']??'') ?>, <?= sanitize($inv['address']??'') ?>, GSTIN: <?= sanitize($inv['gstin']??'') ?></p>
<table><thead><tr><th>Item</th><th>Sacks</th><th>Gross Wt</th><th>Rate</th><th>Net Wt</th><th>Amount</th></tr></thead><tbody><?php foreach($items as $i): ?><tr><td><?= sanitize($i['paddy_name']) ?></td><td><?= sanitize($i['no_of_sacks']) ?></td><td><?= sanitize($i['total_weight_kg']) ?></td><td><?= sanitize($i['rate_per_sack']) ?></td><td><?= sanitize($i['net_weight_kg']) ?></td><td class="text-end"><?= sanitize(formatIndianCurrency($i['net_amount'])) ?></td></tr><?php endforeach; ?></tbody></table>
<h3 class="text-end">Total: <?= sanitize(formatIndianCurrency($inv['total_amount']??0)) ?></h3></body></html>
