<?php
function url($path=''){ return rtrim(BASE_URL,'/').'/'.ltrim($path,'/'); }
function redirect($path){ header('Location: '.url($path)); exit; }
function csrfToken(){ if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function csrfField(){ return '<input type="hidden" name="csrf_token" value="'.sanitize(csrfToken()).'">'; }
function validateCSRF(){ return isset($_POST['csrf_token'],$_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'],$_POST['csrf_token']); }
function sanitize($i){ return htmlspecialchars((string)$i, ENT_QUOTES, 'UTF-8'); }
function formatIndianCurrency($a){ return '₹'.number_format((float)$a,2); }
function formatDate($d,$f='d-m-Y'){ return date($f,strtotime($d)); }
function generateInvoiceNo($p,$fy){ return $p.'/'.$fy.'/'.str_pad((string)random_int(1,9999),4,'0',STR_PAD_LEFT);} 
function getFinancialYear($date=null){ $t=$date?strtotime($date):time(); $y=(int)date('Y',$t); $m=(int)date('n',$t); if($m<FINANCIAL_YEAR_START_MONTH)$y--; return $y.'-'.substr((string)($y+1),2);} 
function getCurrentUser(){ return $_SESSION['user'] ?? null; }
function getUserPermissions(){ return $_SESSION['permissions'] ?? []; }
function hasPermission($s){ return in_array($s,getUserPermissions(),true) || isAdmin(); }
function isAdmin(){ return (getCurrentUser()['role_name'] ?? '')==='Admin'; }
function flashMessage($t,$m){ $_SESSION['flash']=['type'=>$t,'message'=>$m]; }
function getFlashMessage(){ $f=$_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
function auditLog($a,$t,$rid,$o=[],$n=[]){ try { $db=Database::connection(); $st=$db->prepare('INSERT INTO audit_logs (user_id,action,table_name,record_id,old_values,new_values,ip_address,user_agent,created_at) VALUES (?,?,?,?,?,?,?,?,NOW())'); $u=getCurrentUser(); $st->execute([$u['id']??null,$a,$t,$rid,json_encode($o),json_encode($n),$_SERVER['REMOTE_ADDR']??'',$_SERVER['HTTP_USER_AGENT']??'']); } catch(Throwable $e){} }
function jsonResponse($d,$s=200){ http_response_code($s); header('Content-Type: application/json'); echo json_encode($d); exit; }