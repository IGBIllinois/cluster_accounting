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
$owner_html = "<select name='owner' id='owner_input' class='form-control custom-select'>";
$owner_html .= "<option></option>";
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
<div class='col-sm-4 col-md-4 col-lg-4 col-xl-4'>
<form class='form' name='form' method='post'
	action='<?php echo $_SERVER['PHP_SELF']; ?>'>
	<fieldset>
		<div class='form-group'>
			<label class='col-form-label' for='name_input'>Project Name: </label>
				<input class='form-control' type='text' name='name' id='name_input'
					value='<?php if (isset($_POST['name'])) { echo $_POST['name']; } ?>' autocapitalize='none'>
		</div>
		<div class='form-group'>
			<label class='col-form-label' for='owner_input'>Owner: </label>
				<?php echo $owner_html; ?>
		</div>
		<div class='form-group'>
			<label for='ldap_group_input'>LDAP Group: </label>
				<input class='form-control' type='text' name='ldap_group' id='ldap_group_input'
					value='<?php if(isset($_POST['ldap_group'])) { echo $_POST['ldap_group']; } ?>' autocapitalize='none'>
		</div>
		<div class='form-group'>
			<label for='description_input'>Description: </label>
				<input class='form-control' type='text' name='description' id='description_input'
					value='<?php if(isset($_POST['description'])) { echo $_POST['description']; } ?>'>
		</div>
		<div class='form-check'>
			<input class='form-check-input' type='checkbox' name='bill_project' id='bill_project_input'
				onClick='enable_project_bill();'>
			<label class='form-check-label' for='bill_project_input'>Do not bill project: </label>

		</div>
		<div class='form-row'>
			<label for='cfop_input'>CFOP:</label>
				<div class='col-1'>
				<input class='form-control' type='text' name='cfop_1' id='cfop_input'
					maxlength='1' onKeyUp='cfop_advance_1()'
					value='<?php if (isset($_POST['cfop_1'])) { echo $_POST['cfop_1']; } ?>'>
				</div>
				- 
				<div class='col-2'>
				<input class='form-control' type='text' name='cfop_2'
					id='cfop_input' maxlength='6' onKeyUp='cfop_advance_2()'
					value='<?php if (isset($_POST['cfop_2'])) { echo $_POST['cfop_2']; } ?>'>
				</div>
				- 
				<div class='col-2'>
				<input class='form-control' type='text' name='cfop_3'
					id='cfop_input' maxlength='6' onKeyUp='cfop_advance_3()'
					value='<?php if (isset($_POST['cfop_3'])) { echo $_POST['cfop_3']; } ?>'>
				</div>
				- 
				<div class='col-2'>
				<input class='form-control' type='text' name='cfop_4'
					id='cfop_input' maxlength='6'
					value='<?php if (isset($_POST['cfop_4'])) { echo $_POST['cfop_4']; } ?>'>
				</div>
		</div>
		<div class='form-group'>
			<label for='activity_input'>Activity Code
				(optional):</label>
				<input class='form-control' type='text' name='activity' maxlength='6'
					id='activity_input'
					value='<?php if (isset($_POST['activity'])) { echo $_POST['activity']; } ?>'>
		</div>
		<div class='form-group'>
				<input class='btn btn-primary' type='submit' name='add_project'
					value='Add Project'> <input class='btn btn-warning' type='submit'
					name='cancel_project' value='Cancel'>
		</div>
	</fieldset>
</form>
<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}
?>
</div>
<?php
require_once 'includes/footer.inc.php';
?>

<script type="text/javascript">
        $('#owner_input').select2({
                'placeholder': "Select a Owner"
        });
</script>

