<?php
$pageTitle='Rice Stock';
if(!hasPermission('stock_read')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
$rows=$db->query("SELECT s.product_id,rt.name rice_type,s.packaging_type,s.no_of_bags,s.quantity_kg,s.avg_rate_per_kg,s.total_value FROM stock s LEFT JOIN rice_types rt ON rt.id=s.product_id WHERE s.product_type='rice' ORDER BY rt.name,s.packaging_type")->fetchAll();
?>
<div class="container py-4"><h3>Rice Stock Detail</h3><div class="card"><div class="card-body table-responsive"><table id="riceTable" class="table table-striped"><thead><tr><th>Rice Type</th><th>Packaging</th><th>Bags</th><th>Weight (KG)</th><th>Rate</th><th>Value</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= sanitize($r['rice_type']) ?></td><td><?= sanitize($r['packaging_type']) ?></td><td><?= sanitize($r['no_of_bags']) ?></td><td><?= number_format((float)$r['quantity_kg'],2) ?></td><td><?= sanitize(formatIndianCurrency($r['avg_rate_per_kg'])) ?></td><td><?= sanitize(formatIndianCurrency($r['total_value'])) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jQuery&&$.fn.DataTable){$('#riceTable').DataTable();}});</script>
