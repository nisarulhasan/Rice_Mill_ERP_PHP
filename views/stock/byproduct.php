<?php
$pageTitle='Byproduct Stock';
if(!hasPermission('stock_read')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
$rows=$db->query("SELECT s.product_id,b.name byproduct,s.packaging_type,s.no_of_bags,s.quantity_kg,s.avg_rate_per_kg,s.total_value,w.name warehouse FROM stock s LEFT JOIN byproducts b ON b.id=s.product_id LEFT JOIN warehouses w ON w.id=s.warehouse_id WHERE s.product_type='byproduct' ORDER BY b.name,w.name")->fetchAll();
?>
<div class="container py-4"><h3>Byproduct Stock</h3><div class="card"><div class="card-body table-responsive"><table id="bpTable" class="table table-striped"><thead><tr><th>Byproduct</th><th>Warehouse</th><th>Packaging</th><th>Bags</th><th>Qty (KG)</th><th>Rate</th><th>Value</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= sanitize($r['byproduct']) ?></td><td><?= sanitize($r['warehouse']) ?></td><td><?= sanitize($r['packaging_type']) ?></td><td><?= sanitize($r['no_of_bags']) ?></td><td><?= number_format((float)$r['quantity_kg'],2) ?></td><td><?= sanitize(formatIndianCurrency($r['avg_rate_per_kg'])) ?></td><td><?= sanitize(formatIndianCurrency($r['total_value'])) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jQuery&&$.fn.DataTable){$('#bpTable').DataTable();}});</script>
