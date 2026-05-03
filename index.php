<?php
session_start();
require __DIR__.'/config/app.php';
require __DIR__.'/config/database.php';
require __DIR__.'/includes/helpers.php';
require __DIR__.'/includes/auth.php';

$url = sanitize($_GET['url'] ?? 'dashboard/index');
$segments = array_values(array_filter(explode('/', trim($url, '/'))));
$route = $segments[0] ?? 'dashboard';
$action = $segments[1] ?? 'index';
$publicRoutes=['login','setup','logout','forgot-password'];
if($_SERVER['REQUEST_METHOD']==='POST' && !validateCSRF()){ http_response_code(419); exit('CSRF token mismatch'); }
if(!in_array($route,$publicRoutes,true) && !checkAuth()){ flashMessage('warning','Please login'); redirect('auth/login'); }
$file = __DIR__.'/views/'.$route.'/'.$action.'.php';
if(!file_exists($file)) $file=__DIR__.'/views/errors/404.php';
include $file;
