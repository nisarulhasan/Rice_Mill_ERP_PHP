<?php
$pageTitle='Paddy Stock';
if(!hasPermission('stock_read')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
$rows=$db->query("SELECT s.product_id,pv.name variety,w.name warehouse,s.no_of_bags,s.quantity_kg,s.avg_rate_per_kg,s.total_value FROM stock s LEFT JOIN paddy_varieties pv ON pv.id=s.product_id LEFT JOIN warehouses w ON w.id=s.warehouse_id WHERE s.product_type='paddy' ORDER BY pv.name,w.name")->fetchAll();
?>
<div class="container py-4"><h3>Paddy Stock Detail</h3><div class="card"><div class="card-body table-responsive"><table id="paddyTable" class="table table-striped"><thead><tr><th>Variety</th><th>Warehouse</th><th>Bags</th><th>Weight (Qtl)</th><th>Avg Rate/Kg</th><th>Total Value</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= sanitize($r['variety']) ?></td><td><?= sanitize($r['warehouse']) ?></td><td><?= sanitize($r['no_of_bags']) ?></td><td><?= number_format($r['quantity_kg']/100,2) ?></td><td><?= sanitize(formatIndianCurrency($r['avg_rate_per_kg'])) ?></td><td><?= sanitize(formatIndianCurrency($r['total_value'])) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jQuery&&$.fn.DataTable){$('#paddyTable').DataTable();}});</script>
