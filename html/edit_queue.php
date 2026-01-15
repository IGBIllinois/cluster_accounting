<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

if (isset($_GET['queue_id']) && is_numeric($_GET['queue_id'])) {
	$queue_id = $_GET['queue_id'];
        $queue = new queue($db,$_GET['queue_id']);
	
}

//Create New Queue
if (isset($_POST['edit_queue'])) {
        $_POST = array_map('trim',$_POST);

	$queue = new queue($db,$_POST['queue_id']);
        $result = $queue->create($_POST['name'],$_POST['description'],$_POST['skucode'],$_POST['ldap_group'],$_POST['cpu_cost'],$_POST['mem_cost'],$_POST['gpu_cost'],$ldap);
        if($result['RESULT']) {
                unset($_POST);
        }
}
elseif (isset($_POST['delete_queue'])) {
	$queue = new queue($db,$_POST['queue_id']);
        $queue->disable();

}
elseif (isset($_POST['cancel_queue'])) {
        unset($_POST);

}

if (isset($_GET['queue_id']) && is_numeric($_GET['queue_id'])) {
	$queue = new queue($db,$_GET['queue_id']);
}
else {
	exit;
}

$previous_costs = $queue->get_all_costs();
$previous_costs_html = "";
foreach ($previous_costs as $costs) {
	$previous_costs_html .= "<tr>";
	$previous_costs_html .= "<td>" . $costs['cpu'] . "</td>";
	$previous_costs_html .= "<td>" . $costs['memory'] . "</td>";
	$previous_costs_html .= "<td>" . $costs['gpu'] . "</td>";
	$previous_costs_html .= "<td>" . $costs['time'] . "</td>";

}

require_once 'includes/header.inc.php';
?>

<h3>Queue - <?php echo $queue->get_name(); ?></h3>
<hr>
<div class='col-sm-12 col-md-12 col-lg-12 col-xl-12'>
<form method='post' action='<?php echo $_SERVER['PHP_SELF'] . "?queue_id=" . $queue_id; ?>'>
<div class='card'>
<div class='card-header'>Edit Queue</div>
<div class='card-body'>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='description_input'>Description:</label>
		<div class='col-sm-2'>
		<input class='form-control' type='text' name='description' id='description_input'
			value='<?php if (isset($_POST['description'])) { 
				echo $_POST['description']; 
			}
			else {
				echo $queue->get_description();
			}

		?>'>
		</div>
	</div>
	<div class='mb-3 row'>
                <label class='col-sm-2 form-label' for='description_input'>FBS SKU Code (optional):</label>
		<div class='col-sm-2'>
                <input class='form-control' type='text' name='skucode' id='skucode_input'
                        value='<?php if (isset($_POST['skucode'])) { 
					echo $_POST['skucode']; 
			}
			else {
				echo $queue->get_skucode();
			}

			?>'>
		</div>
        </div>

	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='ldap_group_input'>LDAP Group (optional):</label>
		<div class='col-sm-2'>
		<input class='form-control' type='text' name='ldap_group' id='ldap_group_input'
			value='<?php if (isset($_POST['ldap_group'])) { 
				echo $_POST['ldap_group']; 
			} 
			else {
				echo $queue->get_ldap_group();
			}

			?>'>
		</div>
	</div>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='cpu_input'>CPU Cost (per second): </label>
		<div class='col-sm-2'>
		<div class='input-group'>
			<span class='input-group-text'>$</span>
			<input class='form-control' type='text' name='cpu_cost' id='cpu_input'
			value='<?php if (isset($_POST['cpu_cost'])) { 
				echo $_POST['cpu_cost']; 
			} 
			else {
				echo $queue->get_cpu_cost();
			}

			?>' placeholder='0.00'>
		</div>
		</div>
	</div>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='mem_input'>Memory Cost (per gigabyte): </label>
		<div class='col-sm-2'>
		<div class='input-group'>
			<span class='input-group-text'>$</span>
		<input class='form-control' type='text' name='mem_cost' id='mem_input'
			value='<?php if (isset($_POST['mem_cost'])) { 
				echo $_POST['mem_cost']; 
			} 
			else {
				echo $queue->get_mem_cost();
			}

			?>' placeholder='0.00'>
		</div>
		</div>
	</div>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='gpu_input'>GPU Cost (per second): </label>
		<div class='col-sm-2'>
		<div class='input-group'><span class='input-group-text'>$</span>
		<input class='form-control' type='text' name='gpu_cost' id='gpu_input'
			value='<?php if (isset($_POST['gpu_cost'])) { 
				echo $_POST['gpu_cost']; 
			} 
			else {
				echo $queue->get_gpu_cost();
			}
			?>' placeholder='0.00'>
		</div>
		</div>
	</div>
</div>
</div>
<br>
<div class='mb-3 row'>
	<div class='col-sm-8'>
		<input class='btn btn-primary' type='submit' name='edit_queue' value='Edit Queue'>
		<input class='btn btn-danger' type='submit' name='delete_queue' value='Delete Queue' onClick='return (confirm_disable_queue());'>
		<input class='btn btn-warning' type='submit' name='cancel_queue' value='Cancel'>
	</div>
</div>
</form>
<br>
<div class='card'>
<div class='card-header'>Previous Costs</div>
<div class='card-body'>
<table class='table table-striped table-sm table-bordered'>
	<thead>
		<tr><th>CPU (per sec)</th><th>Memory (per GB)</th><th>GPU (per sec)</th><th>Date Added</th></tr>
	</thead>
	<tbody>
	<?php echo $previous_costs_html; ?>
	</tbody>
</table>
</div>
</div>

<?php

if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; }
?>
</div>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
