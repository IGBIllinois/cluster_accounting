<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

$queue_html = "";
$queues = functions::get_queues($db,'ALL');
$queue_html = "";
foreach ($queues as $queue) {
	$queue_html .= "<tr>";
	$queue_html .= "<td><a href='edit_queue.php?queue_id=" . $queue['queue_id'] ."'>" . $queue['name'] . "</a></td>";
	$queue_html .= "<td>" . $queue['description'] . "</td>";
	$queue_html .= "<td>" . $queue['skucode'] . "</td>";
	$queue_html .= "<td>" . $queue['ldap_group'] . "</td>";
	$queue_html .= "<td>" . $queue['cost_cpu_day'] . "</td>";
	$queue_html .= "<td>" . $queue['cost_memory_day'] . "</td>";
	$queue_html .= "<td>" . $queue['cost_gpu_day'] . "</td>";
	$queue_html .= "</tr>\n";
}

require_once 'includes/header.inc.php';

?>

<h3>Queues</h3>
<hr>
<table class='table table-bordered table-striped table-sm'>
<thead>
	<tr><th>Name</th><th>Description</th><th>SKU Code</th><th>LDAP Group</th><th>CPU Cost (per day)</th><th>Memory Cost (per GB)</th><th>GPU Cost (per day)</th></tr>
</thead>
<?php echo $queue_html; ?>

</table>

<?php 
if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; }
?>
</div>

<?php require_once 'includes/footer.inc.php';?>
