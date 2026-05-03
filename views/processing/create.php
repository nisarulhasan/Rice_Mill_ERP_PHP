<?php
if(!hasPermission('processing_create')){http_response_code(403); include __DIR__.'/../errors/403.php'; return;}
$db=Database::connection();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $qty=(float)$_POST['paddy_quantity_kg'];
  $db->beginTransaction();
  try{
    $bn=generateInvoiceNo('BATCH',getFinancialYear());
    $db->prepare("INSERT INTO production_batches (batch_no,batch_date,process_type,paddy_id,warehouse_id,paddy_quantity_kg,machine_name,operator_id,start_time,status,created_by,created_at) VALUES (?,?,?,?,?,?,?,?,?,'in_progress',?,NOW())")
      ->execute([$bn,$_POST['batch_date'],$_POST['process_type'],$_POST['paddy_id'],$_POST['warehouse_id'],$qty,$_POST['machine_name'],$_POST['operator_id'],$_POST['start_time'],getCurrentUser()['id']??null]);
    $db->prepare("UPDATE stock SET quantity_kg=quantity_kg-? WHERE product_type='paddy' AND product_id=? AND warehouse_id=?")
      ->execute([$qty,$_POST['paddy_id'],$_POST['warehouse_id']]);
    $db->commit();
    redirect('processing/index');
  }catch(Throwable $e){$db->rollBack();}
}
?>
<form method='post'><?=csrfField()?> <input type='date' name='batch_date' value='<?=date('Y-m-d')?>'><input name='process_type' value='raw'><input name='paddy_id'><input name='warehouse_id'><input name='paddy_quantity_kg'><input name='machine_name'><input name='operator_id'><input name='start_time'><button>Create</button></form>
