<?php
if(isset($_GET['export']) && $_GET['export']==='csv'){ header('Content-Type:text/csv'); header('Content-Disposition: attachment; filename=report.csv'); $o=fopen('php://output','w'); fputcsv($o,['metric','value']); fputcsv($o,['generated',date('Y-m-d H:i:s')]); exit; }
?>
<div class='container py-4'><h3>Report</h3><a href='?url=reports/<?=basename(__FILE__,'.php')?>&export=csv'>Export CSV</a><canvas id='r'></canvas></div><script src='https://cdn.jsdelivr.net/npm/chart.js'></script><script>new Chart(document.getElementById('r'),{type:'line',data:{labels:['A','B','C'],datasets:[{data:[12,19,14]}]}});</script>
