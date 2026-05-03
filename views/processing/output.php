<?php
if(!hasPermission('processing_update')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $bid=(int)$_POST['batch_id']; $in=(float)$_POST['input_qty']; $rice=(float)$_POST['rice_qty']; $by=(float)$_POST['by_qty'];
  $yield=$in>0?(($rice+$by)/$in)*100:0;
  $db->beginTransaction();
  try{
    $db->prepare('INSERT INTO production_output (batch_id,product_type,rice_type_id,byproduct_id,quantity_kg,percentage_yield,warehouse_id) VALUES (?,?,?,?,?,?,?)')->execute([$bid,'rice',(int)$_POST['rice_type_id'],null,$rice,$yield,(int)$_POST['warehouse_id']]);
    $db->prepare('INSERT INTO production_output (batch_id,product_type,rice_type_id,byproduct_id,quantity_kg,percentage_yield,warehouse_id) VALUES (?,?,?,?,?,?,?)')->execute([$bid,'byproduct',null,(int)$_POST['byproduct_id'],$by,$yield,(int)$_POST['warehouse_id']]);
    $db->prepare("UPDATE production_batches SET status='completed',end_time=NOW() WHERE id=?")->execute([$bid]);
    $db->commit();
  }catch(Throwable $e){$db->rollBack();}
}
?>
<form method='post'><?=csrfField()?> <input name='batch_id'><input name='input_qty'><input name='rice_type_id'><input name='rice_qty'><input name='byproduct_id'><input name='by_qty'><input name='warehouse_id'><button>Save Output</button></form>
