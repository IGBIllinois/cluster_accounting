<?php
require_once 'includes/header.inc.php';


if (!$login_user->is_admin()) {
        exit;
}

if (isset($_POST['delete_project'])) {
	$project_id = $_POST['project_id'];
	$project = new project($db,$project_id);
	$project->disable();
	header('Location: projects.php');
}
elseif (isset($_POST['edit_project'])) {
	foreach ($_POST as $var) {
		$var = trim(rtrim($var));
	}
	$project = new project($db,$_POST['project_id']);
	$bill_project = 1;
	if (isset($_POST['bill_project'])) {
		$bill_project = 0;
	}

        $hide_cfop = 0;
        if (isset($_POST['hide_cfop'])) {
                $hide_cfop = 1;
        }

	$cfop = $_POST['cfop_1'] . "-" . $_POST['cfop_2'] . "-" . $_POST['cfop_3'] . "-" . $_POST['cfop_4'];
	$result = $project->edit($_POST['ldap_group'],$_POST['description'],
			$bill_project,$_POST['owner'],$cfop,$_POST['activity'],$hide_cfop);

}

if (isset($_GET['project_id']) && is_numeric($_GET['project_id'])) {
        $project_id = $_GET['project_id'];
        $project = new project($db,$project_id);
}
else {
	exit;
}

$previous_cfops = $project->get_all_cfops();
$previous_cfops_html = "";
foreach ($previous_cfops as $cfops) {
	$previous_cfops_html .= "<tr><td>" . $cfops['cfop_value'] . "</td>";
	$previous_cfops_html .= "<td>" . $cfops['cfop_activity'] . "</td>";
	if ($cfops['cfop_bill'] == '1') {
		$previous_cfops_html .= "<td><i class='icon-ok'></i></td>";
	}
	else {
		$previous_cfops_html .= "<td><i class='icon-remove'></i></td>";
                                }

	$previous_cfops_html .= "<td>" . $cfops['cfop_time_created'] . "</td>";


}
$users = user_functions::get_users($db);
$owner_html = "";
if ($project->get_default()) {
	$owner_html = "<select name='owner' id='owner_input' class='input' readonly='readonly'>";
}
else {
	$owner_html = "<select name='owner' id='owner_input' class='input' readonly='readonly'>";
}
foreach ($users as $owner) {
	if ($owner['user_name'] == $project->get_owner()) {
		$owner_html .= "<option value='" . $owner['user_id'] . "' selected='selected'>" . $owner['user_name'] . "</option>";
	}	
	else {
        	$owner_html .= "<option value='" . $owner['user_id'] . "'>" . $owner['user_name'] . "</option>";
	}
}
$owner_html .= "</select>";

$group_members = $project->get_group_members($ldap);
$group_members_html = "";
foreach ($group_members as $member) {
	$group_members_html .= "<tr><td>" . $member . "</td></tr>";
}

?>
<h3>
	Project -
	<?php echo $project->get_name(); ?>
</h3>
<table class='table table-bordered table-striped table-condensed'>
	<thead>
		<tr>
			<th>Project Members</th>
		</tr>
	</thead>
	<?php echo $group_members_html; ?>
</table>
<form class='form-horizontal' name='form' method='post'
	action='<?php echo $_SERVER['PHP_SELF']; ?>?project_id=<?php echo $project->get_project_id(); ?>'>
	<input type='hidden' name='project_id'
		value='<?php echo $project->get_project_id(); ?>'>
	<fieldset>
		<legend>Edit Project</legend>
		<div class='control-group'>
			<label class='control-label' for='ldap_group_input'>LDAP Group: </label>
			<div class='controls'>
				<input type='text' name='ldap_group' id='ldap_group_input'
				<?php if ($project->get_default()) { echo "readonly='readonly'"; } ?>
					value='<?php echo $project->get_ldap_group(); ?>'>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='owner_input'>Owner: </label>
			<div class='controls'>
				<?php echo $owner_html; ?>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='description_input'>Description: </label>
			<div class='controls'>
				<input type='text' name='description' id='description_input'
				<?php if ($project->get_default()) { echo "readonly='readonly'"; } ?>
					value='<?php echo $project->get_description(); ?>'>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='bill_project_input'>Do not bill
				project:</label>
			<div class='controls'>
				<input type='checkbox' name='bill_project'
				<?php if (!$project->get_bill_project()) { echo "checked='checked'"; } ?>
					onClick='enable_project_bill();'>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='cfop_input'>CFOP:</label>
			<div class='controls'>
				<input class='input-mini' type='text' name='cfop_1' id='cfop_input'
					maxlength='1' onKeyUp='cfop_advance_1()'
					value='<?php echo $project->get_cfop_college(); ?>'> - <input
					class='input-mini' type='text' name='cfop_2' id='cfop_input'
					maxlength='6' onKeyUp='cfop_advance_2()'
					value='<?php echo $project->get_cfop_fund(); ?>'> - <input
					class='input-mini' type='text' name='cfop_3' id='cfop_input'
					maxlength='6' onKeyUp='cfop_advance_3()'
					value='<?php echo $project->get_cfop_organization(); ?>'> - <input
					class='input-mini' type='text' name='cfop_4' id='cfop_input'
					maxlength='6' value='<?php echo $project->get_cfop_program(); ?>'>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='activity_input'>Activity Code: </label>
			<div class='controls'>
				<input class='input-mini' type='text' name='activity' maxlength='6'
					id='activity_input'
					value='<?php echo $project->get_activity_code(); ?>'>
			</div>
		</div>
		<div class='control-group'>
                        <label class='control-label' for='hide_cfop_input'>Hide CFOP From User:</label>
                        <div class='controls'>
                                <input type='checkbox' name='hide_cfop' <?php if (isset($_POST['hide_cfop'])) { echo "checked='checked'"; } ?>>
                        </div>
                </div>

		<div class='control-group'>
			<div class='controls'>
				<input class='btn btn-primary' type='submit' name='edit_project'
					value='Edit Project'>
				<?php if (!$project->get_default()) {
					echo "<input class='btn btn-danger' type='submit'
					name='delete_project' value='Delete Project'>";
			} ?>
			</div>
		</div>
	</fieldset>
</form>
<hr>
<h3>Previous CFOPs</h3>
<table class='table table-striped table-condensed table-bordered'>
        <thead>
                <tr>
                        <th>CFOP</th>
                        <th>Activity Code</th>
                        <th>Bill Project</th>
                        <th>Date Added</th>
                </tr>
        </thead>
        <?php echo $previous_cfops_html; ?>


</table>

<?php if (isset($_SERVER['HTTP_REFERER'])) {
        echo "<a href='" . $_SERVER['HTTP_REFERER'] . "' class='btn btn-primary'>Back</a>";

}

?>

<script type='text/javascript'>
enable_project_bill();
</script>

<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}

require_once 'includes/footer.inc.php';
?>
