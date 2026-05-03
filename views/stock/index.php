<?php
$pageTitle='Stock Dashboard';
if(!hasPermission('stock_read')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
$summary=$db->query("SELECT product_type, SUM(quantity_kg) qty, SUM(total_value) val FROM stock GROUP BY product_type")->fetchAll();
$map=['paddy'=>['qty'=>0,'val'=>0],'rice'=>['qty'=>0,'val'=>0],'byproduct'=>['qty'=>0,'val'=>0]];
foreach($summary as $s){$map[$s['product_type']] = ['qty'=>(float)$s['qty'],'val'=>(float)$s['val']];}
$packaging=$db->query("SELECT packaging_type, SUM(quantity_kg) qty FROM stock WHERE product_type='rice' GROUP BY packaging_type")->fetchAll();
?>
<div class="container py-4"><h3>Stock Dashboard</h3>
<div class="row g-3 mb-3"><?php foreach($map as $k=>$v): ?><div class="col-md-4"><div class="card"><div class="card-body"><h6><?= ucfirst($k) ?></h6><h4><?= number_format($v['qty']/100,2) ?> Qtl</h4><p><?= sanitize(formatIndianCurrency($v['val'])) ?></p></div></div></div><?php endforeach; ?></div>
<div class="card"><div class="card-header">Rice Packaging Breakdown</div><div class="card-body table-responsive"><table class="table table-sm"><thead><tr><th>Packaging</th><th>Qty (KG)</th></tr></thead><tbody><?php foreach($packaging as $p): ?><tr><td><?= sanitize($p['packaging_type']) ?></td><td><?= number_format((float)$p['qty'],2) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
