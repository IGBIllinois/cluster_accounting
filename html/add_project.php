<?php
require_once 'includes/header.inc.php';


if (!$login_user->is_admin()) {
        exit;
}
if (isset($_POST['add_project'])) {
	foreach ($_POST as $var) {
		$var = trim(rtrim($var));
	}

	$project = new project($db);

	if (isset($_POST['bill_project'])) {
		$bill_project = 0;
		$default = 0;
		$result = $project->create($_POST['name'],$_POST['ldap_group'],
					$_POST['description'],$default,$bill_project,$_POST['owner']);
	}
	else {
		$bill_project = 1;
		$default = 0;
		$cfop = $_POST['cfop_1'] . "-" . $_POST['cfop_2'] . "-" . $_POST['cfop_3'] . "-" . $_POST['cfop_4'];
		$result = $project->create($_POST['name'],$_POST['ldap_group'],$_POST['description'],
					$default,$bill_project,$_POST['owner'],$cfop,$_POST['activity']);
	}

	if ($result['RESULT']) {
		unset($_POST);
	}
}

elseif (isset($_POST['cancel_project'])) {
	unset($_POST);
}


$users = user_functions::get_users($db);
$owner_html = "<select name='owner' id='owner_input' class='input'>";
foreach ($users as $owner) {
	if ((isset($_POST['owner'])) && ($_POST['owner'] == $owner['user_id'])) {
		$owner_html .= "<option value='" . $owner['user_id'] . "' selected='selected'>" . $owner['user_name'] . "</option>";
	}
	else {
		$owner_html .= "<option value='" . $owner['user_id'] . "'>" . $owner['user_name'] . "</option>";
	}

}
$owner_html .= "</select>";
?>
<h3>Add Project</h3>

<form class='form-horizontal' name='form' method='post'
	action='<?php echo $_SERVER['PHP_SELF']; ?>'>
	<fieldset>
		<legend>Add Project</legend>
		<div class='control-group'>
			<label class='control-label' for='name_input'>Project Name: </label>
			<div class='controls'>
				<input type='text' name='name' id='name_input'
					value='<?php if (isset($_POST['name'])) { echo $_POST['name']; } ?>'>
			</div>
		</div>
				<div class='control-group'>
			<label class='control-label' for='owner_input'>Owner: </label>
			<div class='controls'>
				<?php echo $owner_html; ?>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='ldap_group_input'>LDAP Group: </label>
			<div class='controls'>
				<input type='text' name='ldap_group' id='ldap_group_input'
					value='<?php if(isset($_POST['ldap_group'])) { echo $_POST['ldap_group']; } ?>'>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='description_input'>Description: </label>
			<div class='controls'>
				<input type='text' name='description' id='description_input'
					value='<?php if(isset($_POST['description'])) { echo $_POST['description']; } ?>'>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='bill_project_input'>Do not bill
				project: </label>
			<div class='controls'>
				<input type='checkbox' name='bill_project' id='bill_project_input'
					onClick='enable_project_bill();'>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='cfop_input'>CFOP:</label>
			<div class='controls'>
				<input class='input-mini' type='text' name='cfop_1' id='cfop_input'
					maxlength='1' onKeyUp='cfop_advance_1()'
					value='<?php if (isset($_POST['cfop_1'])) { echo $_POST['cfop_1']; } ?>'>
				- <input class='input-mini' type='text' name='cfop_2'
					id='cfop_input' maxlength='6' onKeyUp='cfop_advance_2()'
					value='<?php if (isset($_POST['cfop_2'])) { echo $_POST['cfop_2']; } ?>'>
				- <input class='input-mini' type='text' name='cfop_3'
					id='cfop_input' maxlength='6' onKeyUp='cfop_advance_3()'
					value='<?php if (isset($_POST['cfop_3'])) { echo $_POST['cfop_3']; } ?>'>
				- <input class='input-mini' type='text' name='cfop_4'
					id='cfop_input' maxlength='6'
					value='<?php if (isset($_POST['cfop_4'])) { echo $_POST['cfop_4']; } ?>'>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='activity_input'>Activity Code
				(optional):</label>
			<div class='controls'>
				<input class='input-mini' type='text' name='activity' maxlength='6'
					id='activity_input'
					value='<?php if (isset($_POST['activity'])) { echo $_POST['activity']; } ?>'>
			</div>
		</div>
		<div class='control-group'>
			<div class='controls'>
				<input class='btn btn-primary' type='submit' name='add_project'
					value='Add Project'> <input class='btn btn-warning' type='submit'
					name='cancel_project' value='Cancel'>
			</div>
		</div>
	</fieldset>
</form>
<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}


require_once 'includes/footer.inc.php';
?>
