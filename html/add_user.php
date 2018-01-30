<?php
require_once 'includes/header.inc.php';

if (!$login_user->is_admin()) {
	exit;
}

$message = "";
if (isset($_POST['add_user'])) {
	foreach($_POST as $var) {
		$var = trim(rtrim($var));
	}
	$admin = 0;
	if (isset($_POST['is_admin'])) {
		$admin = 1;
	}
	//sets supervisor_id to 0 if the new user is a supervisor
	if (isset($_POST['is_supervisor'])) {
		$supervisor_id = 0;
	}
	else {
		$supervisor_id = $_POST['supervisor_id'];
	}

	//determines if the project will be billed.
	$user = new user($db,$ldap);
	$bill_project = 1;
	if (isset($_POST['bill_project'])) {
		$bill_project = 0;
	}
	
	$hide_cfop = 0;
	if (isset($_POST['hide_cfop'])) {
		$hide_cfop = 1;
	}
	$cfop = $_POST['cfop_1'] . "-" . $_POST['cfop_2'] . "-" . $_POST['cfop_3'] . "-" . $_POST['cfop_4'];

	$result = $user->create($_POST['new_username'],$supervisor_id,$admin,$bill_project,$cfop,$_POST['activity'],$hide_cfop);

	if ($result['RESULT'] == true) {
		header("Location: user.php?user_id=" . $result['user_id']);
	}
	elseif ($result['RESULT'] == false) {
		$message = $result['MESSAGE'];
	}
}
elseif (isset($_POST['cancel_user'])) {
	unset($_POST);
}

//Code to get list of supervisors to choose from.
$supervisors = user_functions::get_supervisors($db);
$supervisors_html = "<select class='input-xlarge' name='supervisor_id' id='supervisors_input'>";
$supervisors_html .= "<option value='-1'></option>";
foreach ($supervisors as $supervisor) {
	$supervisor_id = $supervisor['id'];
	$supervisor_fullname = $supervisor['full_name'];
	$supervisor_username = $supervisor['username'];
	if ((isset($_POST['supervisor_id'])) && (($_POST['supervisor_id']) == $supervisor_id)) {
		$supervisors_html .= "<option value='" . $supervisor_id . "' selected='selected'>";
		$supervisors_html .= $supervisor_username . " - " . $supervisor_fullname . "</option>";
	}
	else {
		$supervisors_html .= "<option value='" . $supervisor_id . "'>";
		$supervisors_html .= $supervisor_username . " - " . $supervisor_fullname . "</option>";
	}
}
$supervisors_html .= "</select>";


?>

<form class='form-horizontal' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'
	name='form'>
	<fieldset>
		<legend>Add User</legend>
		<div class='control-group'>
			<label class='control-label' for='username_input'>Username:</label>
			<div class='controls'>
				<input type='text' name='new_username' id='username_input'
					value='<?php if (isset($_POST['new_username'])) { echo $_POST['new_username']; } ?>'>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='admin_input'>Is Administrator:</label>
			<div class='controls'>
				<input type='checkbox' name='is_admin' id='is_admin_input'
					<?php if (isset($_POST['is_admin'])) { echo "checked='checked'"; } ?>>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='is_supervisor_input'>Is Supervisor</label>
			<div class='controls'>
				<input type='checkbox' name='is_supervisor' id='is_supervisor_input'
					onClick='enable_supervisors();' <?php if (isset($_POST['is_supervisor'])) { echo "checked='checked'"; } ?>>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='supervisor_input'>Supervisor:</label>
			<div class='controls'>
				<?php echo $supervisors_html; ?>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='bill_project_input'>Do not bill
				default project:</label>
			<div class='controls'>
				<input type='checkbox' name='bill_project'
					onClick='enable_project_bill();' <?php if (isset($_POST['bill_project'])) { echo "checked='checked'"; } ?>>
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
			<label class='control-label' for='hide_cfop_input'>Hide CFOP From User:</label>
			<div class='controls'>
				<input type='checkbox' name='hide_cfop' <?php if (isset($_POST['hide_cfop'])) { echo "checked='checked'"; } ?>>
			</div>
		</div>
		<div class='control-group'>
			<div class='controls'>
				<input class='btn btn-primary' type='submit' name='add_user'
					value='Add User'> <input class='btn btn-warning' type='submit'
					name='cancel_user' value='Cancel'>
			</div>
		</div>
	</fieldset>
</form>
<script type='text/javascript'>
enable_supervisors();

</script>
<?php
if (isset($message)) { echo $message; }
include_once 'includes/footer.inc.php';
?>
