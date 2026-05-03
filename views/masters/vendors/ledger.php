<?php
$pageTitle='Vendor Ledger';
if(!hasPermission('vendor_read')){http_response_code(403); include __DIR__.'/../../errors/403.php'; return;}
$db=Database::connection();
$id=(int)($_GET['id']??0); if(!$id && isset($segments[3])) $id=(int)$segments[3];
$v=$db->prepare('SELECT * FROM vendors WHERE id=? LIMIT 1'); $v->execute([$id]); $vendor=$v->fetch();
if(!$vendor){ flashMessage('warning','Vendor not found.'); redirect('masters/vendors/index'); }

$entries=[];
try {
  $p=$db->prepare("SELECT invoice_date AS txn_date, invoice_no AS ref_no, 'Purchase Invoice' AS narration, total_amount AS debit, 0 AS credit FROM purchase_invoices WHERE vendor_id=?");
  $p->execute([$id]); $entries=array_merge($entries,$p->fetchAll());
} catch(Throwable $e){}

usort($entries, fn($a,$b)=>strcmp((string)$a['txn_date'],(string)$b['txn_date']));
$opening=(float)$vendor['opening_balance'] * (($vendor['balance_type']==='dr')?1:-1);
$running=$opening;
?>
<div class="container py-4">
  <div class="d-flex justify-content-between mb-3"><h3>Vendor Ledger - <?= sanitize($vendor['company_name']) ?></h3><a class="btn btn-secondary" href="<?= sanitize(url('masters/vendors/index')) ?>">Back</a></div>
  <div class="card mb-3"><div class="card-body">
    <div class="row"><div class="col-md-4"><strong>Opening Balance:</strong> <?= sanitize(formatIndianCurrency(abs($opening))) ?> <?= $opening>=0?'Dr':'Cr' ?></div>
    <div class="col-md-4"><strong>Credit Period:</strong> <?= (int)$vendor['credit_period_days'] ?> days</div>
    <div class="col-md-4"><strong>GSTIN:</strong> <?= sanitize($vendor['gstin']) ?></div></div>
  </div></div>
  <div class="card"><div class="card-body table-responsive">
    <table id="ledgerTable" class="table table-bordered table-sm align-middle"><thead class="table-light"><tr><th>Date</th><th>Reference</th><th>Narration</th><th>Debit</th><th>Credit</th><th>Running Balance</th></tr></thead><tbody>
      <?php foreach($entries as $e): $running += (float)$e['debit'] - (float)$e['credit']; ?>
      <tr><td><?= sanitize($e['txn_date'] ? formatDate($e['txn_date']) : '-') ?></td><td><?= sanitize($e['ref_no']) ?></td><td><?= sanitize($e['narration']) ?></td><td><?= sanitize(formatIndianCurrency($e['debit'])) ?></td><td><?= sanitize(formatIndianCurrency($e['credit'])) ?></td><td><?= sanitize(formatIndianCurrency(abs($running))) ?> <?= $running>=0?'Dr':'Cr' ?></td></tr>
      <?php endforeach; ?>
    </tbody></table>
  </div></div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>{if(window.jQuery&&$.fn.DataTable){$('#ledgerTable').DataTable({order:[[0,'asc']]});}});</script>
