<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

if (isset($_POST['update_cost'])) {
	$data_cost = new data_cost($db,$_POST['data_cost_id']);
	$result = $data_cost->update_cost($_POST['cost']);

}

$data_cost = data_functions::get_current_data_cost($db);
require_once 'includes/header.inc.php';
?>
<h3>Data Storage Cost</h3>
<hr>
<div class='col-sm-6'>
<form class='form' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<input type='hidden' name='data_cost_id' value='<?php echo $data_cost->get_id(); ?>'>
<table class='table table-bordered table-striped table-sm'>
<thead>
<tr><th>Cost (per TB)</th><th>Time Set</th></tr>
</thead>
<tr>
<td>$<?php echo $data_cost->get_cost(); ?></td>
<td><?php echo $data_cost->get_time_created(); ?></td>
</tr>
<tr><td><div class='input-group'><span class='input-group-text'>$</span><input class='form-control' type='text' name='cost'></div></td>
<td><input class='btn btn-primary' type='submit' name='update_cost' value='Update Cost' onClick='return confirm_update_data_cost();'></td></tr>
</table>
</form>
</div>

<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}

?>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
