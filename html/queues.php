<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

//Set New Cost on Queue
if (isset($_POST['set_cost'])) {

	$result = false;
	$queue = new queue($db,$_POST['queue_id']);
	$result = $queue->update_cost($_POST['cpu_cost'],$_POST['mem_cost'],$_POST['gpu_cost']);

}
elseif (isset($_POST['delete_queue'])) {
	$queue = new queue($db,$_POST['queue_id']);
	$queue->disable();

}

$queue_html = "";
$queues = functions::get_queues($db,'ALL');

foreach ($queues as $queue) {

	$queue_object = new queue($db,$queue['queue_id']);
	$all_costs = $queue_object->get_all_costs();
	$queue_html .= "<form method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
	$queue_html .= "<input type='hidden' name='queue_id' value='" . $queue_object->get_queue_id() . "'>";
	$queue_html .= "<table class='table table-bordered table-striped table-sm'>";
	$queue_html .= "<thead><tr><th colspan='4'>" . $queue_object->get_name() . " - "  . $queue_object->get_description() . "</th></tr></thead>";
	$queue_html .= "<tr><td>CPU Cost (per sec)</td>";
	$queue_html .= "<td>Memory Cost (per GB)</td>";
	$queue_html .= "<td>GPU Cost (per sec)</td>";
	$queue_html .= "<td>Time Set</td></tr>";
	foreach ($all_costs as $cost) {
		$queue_html .= "<tr><td>$" . $cost['cpu'] . "</td>";
		$queue_html .= "<td>$" . $cost['memory'] . "</td>";
		$queue_html .= "<td>$" . $cost['gpu'] . "</td>";
		$queue_html .= "<td>" . $cost['time'] . "</td>";
		$queue_html .= "</tr>";
	}
	$queue_html .= "<tr><td><div class='input-group'><div class='input-group-prepend'><div class='input-group-text'>$</div></div><input class='form-control' type='text' name='cpu_cost'</div></td>";
	$queue_html .= "<td><div class='input-group'><div class='input-group-prepend'><div class='input-group-text'>$</div></div><input class='form-control' type='text' name='mem_cost''></div></td>";
	$queue_html .= "<td><div class='input-group'><div class='input-group-prepend'><div class='input-group-text'>$</div></div><input class='form-control' type='text' name='gpu_cost'></div></td>";
	$queue_html .= "<td><button class='btn btn-small btn-primary' type='submit' name='set_cost'><i class='fas fa-edit'></i>&nbsp;Update Cost</button>&nbsp;";
	$queue_html .= "<button class='btn btn-small btn-danger' type='submit' name='delete_queue' onClick='return confirm_delete_queue()'><i class='fas fa-times'></i>&nbsp;Delete</button></td></tr>";
	$queue_html .= "</table></form>";
	$queue_html .= "<br>";
}

require_once 'includes/header.inc.php';

?>

<h3>Queues</h3>
<hr>
<?php echo $queue_html;


if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; }

require_once 'includes/footer.inc.php';?>
