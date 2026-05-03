<?php
$pageTitle='Stock Movements';
if(!hasPermission('stock_read')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
$rows=$db->query("SELECT sm.*,w.name warehouse FROM stock_movements sm LEFT JOIN warehouses w ON w.id=sm.warehouse_id ORDER BY sm.transaction_date, sm.id")->fetchAll();
$running=[];
?>
<div class="container py-4"><h3>Stock Movement Register</h3><div class="card"><div class="card-body table-responsive"><table id="mvTable" class="table table-striped table-sm"><thead><tr><th>Date</th><th>Type</th><th>Product Type</th><th>Product ID</th><th>Warehouse</th><th>Movement</th><th>Qty (KG)</th><th>Rate</th><th>Running Bal (KG)</th><th>Reference</th></tr></thead><tbody>
<?php foreach($rows as $r): $key=$r['product_type'].'-'.$r['product_id'].'-'.$r['warehouse_id']; $running[$key]=$running[$key]??0; $delta=in_array($r['movement_type'],['in','purchase','production_in'],true)?(float)$r['quantity_kg']:-(float)$r['quantity_kg']; $running[$key]+=$delta; ?>
<tr><td><?= sanitize(formatDate($r['transaction_date'])) ?></td><td><span class="badge bg-<?= $delta>=0?'success':'danger' ?>"><?= $delta>=0?'IN':'OUT' ?></span></td><td><?= sanitize($r['product_type']) ?></td><td><?= (int)$r['product_id'] ?></td><td><?= sanitize($r['warehouse']) ?></td><td><?= sanitize($r['movement_type']) ?></td><td><?= number_format((float)$r['quantity_kg'],2) ?></td><td><?= sanitize(formatIndianCurrency($r['rate_per_kg'])) ?></td><td><?= number_format($running[$key],2) ?></td><td><?= sanitize($r['reference_type'].'#'.$r['reference_id']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div></div>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jQuery&&$.fn.DataTable){$('#mvTable').DataTable({order:[[0,'desc']]});}});</script>
