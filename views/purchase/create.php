<?php
$pageTitle='Create Purchase';
if(!hasPermission('purchase_create')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
$vendors=$db->query('SELECT id,company_name,gstin,phone FROM vendors WHERE is_active=1 ORDER BY company_name')->fetchAll();
$paddy=$db->query('SELECT id,name FROM paddy_varieties WHERE is_active=1 ORDER BY name')->fetchAll();
$warehouses=$db->query('SELECT id,name FROM warehouses WHERE is_active=1 ORDER BY name')->fetchAll();
$errors=[];

if($_SERVER['REQUEST_METHOD']==='POST'){
  $invoiceDate=$_POST['invoice_date']??date('Y-m-d');
  $vendorId=(int)($_POST['vendor_id']??0);
  $warehouseId=(int)($_POST['warehouse_id']??0);
  $truck=trim($_POST['truck_no']??'');
  $driver=trim($_POST['driver_name']??'');
  $driverPhone=trim($_POST['driver_phone']??'');
  $freight=(float)($_POST['freight_charges']??0);
  $labour=(float)($_POST['labour_charges']??0);
  $other=(float)($_POST['other_charges']??0);
  $otherNarr=trim($_POST['other_charges_narration']??'');
  $discount=(float)($_POST['discount_amount']??0);
  $status=$_POST['status']??'final';
  $items=$_POST['items']??[];
  if($vendorId<=0) $errors[]='Vendor is required.';
  if($warehouseId<=0) $errors[]='Warehouse is required.';
  if(!$items || !is_array($items)) $errors[]='At least one item is required.';

  $cleanItems=[]; $subTotal=0; $totalQty=0;
  foreach($items as $it){
    $pid=(int)($it['paddy_id']??0); $sacks=(float)($it['no_of_sacks']??0); $wt=(float)($it['weight_per_sack_kg']??0); $rate=(float)($it['rate_per_sack']??0);
    $moist=(float)($it['moisture_percentage']??0); $ded=(float)($it['deduction_percentage']??0);
    if($pid<=0 || $sacks<=0 || $wt<=0 || $rate<0) continue;
    $grossWt=$sacks*$wt; $rateQ=$wt>0?($rate/($wt/100)):0; $grossAmt=$sacks*$rate;
    $netWt=$grossWt-(($grossWt*$ded)/100); $rateKg=$rate/$wt; $netAmt=$netWt*$rateKg;
    $cleanItems[]=compact('pid','sacks','wt','rate','moist','ded','grossWt','rateQ','grossAmt','netWt','netAmt');
    $subTotal+=$netAmt; $totalQty+=$netWt;
  }
  if(!$cleanItems) $errors[]='No valid item rows found.';

  if(!$errors){
    $fy=getFinancialYear($invoiceDate); $invoiceNo=generateInvoiceNo('PUR',$fy);
    $total=max(0,$subTotal+$freight+$labour+$other-$discount);
    $db->beginTransaction();
    try {
      $inv=$db->prepare('INSERT INTO purchase_invoices (invoice_no,invoice_date,vendor_id,truck_no,driver_name,driver_phone,freight_charges,labour_charges,other_charges,other_charges_narration,discount_amount,total_amount,warehouse_id,status,financial_year,created_by,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
      $inv->execute([$invoiceNo,$invoiceDate,$vendorId,$truck,$driver,$driverPhone,$freight,$labour,$other,$otherNarr,$discount,$total,$warehouseId,$status,$fy,getCurrentUser()['id']??null]);
      $purchaseId=(int)$db->lastInsertId();

      $itm=$db->prepare('INSERT INTO purchase_items (purchase_id,paddy_id,no_of_sacks,weight_per_sack_kg,total_weight_kg,rate_per_sack,rate_per_quintal,gross_amount,moisture_percentage,deduction_percentage,net_weight_kg,net_amount) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
      foreach($cleanItems as $row){
        $itm->execute([$purchaseId,$row['pid'],$row['sacks'],$row['wt'],$row['grossWt'],$row['rate'],$row['rateQ'],$row['grossAmt'],$row['moist'],$row['ded'],$row['netWt'],$row['netAmt']]);
        $avg=$row['netWt']>0?$row['netAmt']/$row['netWt']:0;
        $stock=$db->prepare("INSERT INTO stock (product_type,product_id,packaging_type,warehouse_id,quantity_kg,no_of_bags,avg_rate_per_kg,total_value,last_updated) VALUES ('paddy',?,?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE quantity_kg=quantity_kg+VALUES(quantity_kg), no_of_bags=no_of_bags+VALUES(no_of_bags), total_value=total_value+VALUES(total_value), avg_rate_per_kg=IF(quantity_kg+VALUES(quantity_kg)>0,(total_value+VALUES(total_value))/(quantity_kg+VALUES(quantity_kg)),0), last_updated=NOW()");
        $stock->execute([$row['pid'],'Loose',$warehouseId,$row['netWt'],$row['sacks'],$avg,$row['netAmt']]);
        $mv=$db->prepare("INSERT INTO stock_movements (product_type,product_id,packaging_type,warehouse_id,movement_type,reference_type,reference_id,quantity_kg,rate_per_kg,balance_quantity_kg,transaction_date,created_by) VALUES ('paddy',?,?,?,?,?,?,?,?,?,?,?)");
        $mv->execute([$row['pid'],'Loose',$warehouseId,'in','purchase',$purchaseId,$row['netWt'],$avg,0,$invoiceDate,getCurrentUser()['id']??null]);
      }

      $vchNo=generateInvoiceNo('VCH',$fy);
      $vch=$db->prepare("INSERT INTO vouchers (voucher_no,voucher_date,voucher_type,reference_table,reference_id,narration,total_debit,total_credit,financial_year,created_by,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())");
      $vch->execute([$vchNo,$invoiceDate,'payment','purchase_invoices',$purchaseId,'Purchase posting',$total,$total,$fy,getCurrentUser()['id']??null]);
      $voucherId=(int)$db->lastInsertId();
      $ve=$db->prepare('INSERT INTO voucher_entries (voucher_id,account_id,debit_amount,credit_amount,party_type,party_id,narration) VALUES (?,?,?,?,?,?,?)');
      $ve->execute([$voucherId,1,$total,0,'vendor',$vendorId,'Purchase A/c Dr']);
      $ve->execute([$voucherId,2,0,$total,'vendor',$vendorId,'Vendor A/c Cr']);

      auditLog('create','purchase_invoices',$purchaseId,[],['invoice_no'=>$invoiceNo,'total'=>$total]);
      $db->commit(); flashMessage('success','Purchase saved successfully.'); redirect('purchase/view/'.$purchaseId);
    } catch(Throwable $e){ if($db->inTransaction()) $db->rollBack(); $errors[]='Failed to save purchase: '.$e->getMessage(); }
  }
}
?>
<div class="container py-4"><h3>Create Purchase</h3><?php foreach($errors as $e): ?><div class="alert alert-danger"><?= sanitize($e) ?></div><?php endforeach; ?>
<form method="post" class="card card-body"><?= csrfField() ?>
<div class="row g-3"><div class="col-md-3"><label class="form-label">Date</label><input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>" class="form-control"></div>
<div class="col-md-4"><label class="form-label">Vendor</label><select name="vendor_id" class="form-select select2" required><option value="">Select Vendor</option><?php foreach($vendors as $v): ?><option value="<?= $v['id'] ?>"><?= sanitize($v['company_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Warehouse</label><select name="warehouse_id" class="form-select" required><?php foreach($warehouses as $w): ?><option value="<?= $w['id'] ?>"><?= sanitize($w['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="final">Final</option><option value="draft">Draft</option></select></div></div>
<hr><h5>Items</h5>
<table class="table" id="itemsTable"><thead><tr><th>Paddy</th><th>Sacks</th><th>Wt/Sack</th><th>Total Wt</th><th>Rate/Sack</th><th>Ded%</th><th>Net Wt</th><th>Net Amount</th><th></th></tr></thead><tbody></tbody></table>
<button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">Add Row</button>
<div class="row g-3 mt-3"><div class="col-md-2"><input name="freight_charges" placeholder="Freight" value="0" class="form-control calc-extra"></div><div class="col-md-2"><input name="labour_charges" placeholder="Labour" value="0" class="form-control calc-extra"></div><div class="col-md-2"><input name="other_charges" placeholder="Other" value="0" class="form-control calc-extra"></div><div class="col-md-2"><input name="discount_amount" placeholder="Discount" value="0" class="form-control calc-extra"></div><div class="col-md-4 text-end"><h4>Total: ₹<span id="grandTotal">0.00</span></h4></div></div>
<div class="mt-3"><button class="btn btn-success">Save Purchase</button></div></form></div>
<script>
const paddyOptions = `<?php foreach($paddy as $p){ echo '<option value="'.$p['id'].'">'.sanitize($p['name']).'</option>'; } ?>`;
function addRow(){const tr=document.createElement('tr');tr.innerHTML=`<td><select name="items[][paddy_id]" class="form-select">${paddyOptions}</select></td><td><input name="items[][no_of_sacks]" type="number" step="0.01" class="form-control calc" value="0"></td><td><input name="items[][weight_per_sack_kg]" type="number" step="0.01" class="form-control calc" value="50"></td><td><input class="form-control totalwt" readonly></td><td><input name="items[][rate_per_sack]" type="number" step="0.01" class="form-control calc" value="0"></td><td><input name="items[][deduction_percentage]" type="number" step="0.01" class="form-control calc" value="0"></td><td><input name="items[][net_weight_kg]" class="form-control netwt" readonly></td><td><input name="items[][net_amount]" class="form-control netamt" readonly></td><td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); calcAll();">x</button></td>`;document.querySelector('#itemsTable tbody').appendChild(tr);bindCalc(tr);calcAll();}
function bindCalc(tr){tr.querySelectorAll('.calc').forEach(i=>i.addEventListener('input',calcAll)); document.querySelectorAll('.calc-extra').forEach(i=>i.addEventListener('input',calcAll));}
function calcAll(){let sum=0;document.querySelectorAll('#itemsTable tbody tr').forEach(tr=>{const sacks=parseFloat(tr.children[1].querySelector('input').value)||0;const wt=parseFloat(tr.children[2].querySelector('input').value)||0;const rate=parseFloat(tr.children[4].querySelector('input').value)||0;const ded=parseFloat(tr.children[5].querySelector('input').value)||0;const gross=sacks*wt;const net=gross-(gross*ded/100);const amt=wt>0?net*(rate/wt):0;tr.querySelector('.totalwt').value=gross.toFixed(2);tr.querySelector('.netwt').value=net.toFixed(2);tr.querySelector('.netamt').value=amt.toFixed(2);sum+=amt;});const f=parseFloat(document.querySelector('[name="freight_charges"]').value)||0;const l=parseFloat(document.querySelector('[name="labour_charges"]').value)||0;const o=parseFloat(document.querySelector('[name="other_charges"]').value)||0;const d=parseFloat(document.querySelector('[name="discount_amount"]').value)||0;document.getElementById('grandTotal').textContent=(sum+f+l+o-d).toFixed(2);}
addRow();
</script>
