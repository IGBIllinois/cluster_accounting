<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

if (isset($_POST['update_cost'])) {
	$data_cost = new data_cost($db,$_POST['data_cost_id']);
	$result = $data_cost->update_cost($_POST['cost']);

}

$data_costs = data_functions::get_data_costs($db);
$data_costs_html = "";
foreach ($data_costs as $data_cost) {
	$cost_object = new data_cost($db,$data_cost['id']);

	$data_costs_html .= "<form class='form' method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
	$data_costs_html .= "<input type='hidden' name='data_cost_id' value='" . $cost_object->get_data_cost_id() . "'>";
	$data_costs_html .= "<table class='table table-bordered table-striped table-sm'>";
	$data_costs_html .= "<thead><th colspan='2'>" . $cost_object->get_type() . "</th>";
	$data_costs_html .= "<tr><th>Cost (per TB)</th><th>Time Set</th></tr>";
	$data_costs_html .= "</thead>";
	$data_costs_html .= "<tr>";
	$data_costs_html .= "<td>$" . $cost_object->get_formatted_cost() . "</td>";
	$data_costs_html .= "<td>" . $cost_object->get_time_created() . "</td>";
	$data_costs_html .= "</tr>";
	$data_costs_html .= "<tr><td><div class='input-group'><div class='input-group-prepend'><div class='input-group-text'>$</div></div><input class='form-control' type='text' name='cost'></div></td>";
	$data_costs_html .= "<td><input class='btn btn-primary' type='submit' name='update_cost' value='Update Cost'></td></tr>";
	$data_costs_html .= "</table>";
	$data_costs_html .= "</form>";

}

require_once 'includes/header.inc.php';
?>
<h3>Data Cost</h3>
<hr>
<div class='col-sm-4 col-md-4 col-lg-4 col-xl-4'>
<?php echo $data_costs_html; ?>
</div>

<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}
require_once 'includes/footer.inc.php';
?>
