<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
	exit;
}

if (isset($_GET['user_id'])) {

	$user_id = $_GET['user_id'];
	$user = new user($db,$ldap,$user_id);
}
$message = "";
if (isset($_POST['edit_user'])) {
	foreach($_POST as $var) {
		$var = trim(rtrim($var));
	}
	$admin = 0;
	if (isset($_POST['is_admin'])) {
		$admin = 1;
	}
	//sets supervisor_id to 0 if the user is a supervisor
	$is_supervisor = 0;
	if (isset($_POST['is_supervisor'])) {
		$is_supervisor = 1;
		$supervisor_id = 0;
	}
	else {
		$supervisor_id = $_POST['supervisor_id'];
	}

	$user = new user($db,$ldap,$_POST['user_id']);
	if ($user->is_admin() != $admin) {
		if ($user->set_admin($admin)) {
			$message = "<div class='alert alert-success'>User Administrator successfully set</div>";
		}
	}
	if (($user->is_supervisor() != $is_supervisor) || ($user->get_supervisor_id() != $supervisor_id)) {
		if ($user->set_supervisor($supervisor_id)) {
			$message .= "<div class='alert alert-success'>User Supervisor successfully set</div>";
		}
	}
}
//Deletes user
elseif (isset($_POST['delete_user'])) {
        $result = $user->disable();
        if ($result['RESULT']) {
                header("Location: list_users.php");
        }
	else {
		$message = "<div class='alert alert-error'>" . $result['MESSAGE'] . "</div>";
	}
}

elseif (isset($_POST['cancel_user'])) {
	unset($_POST);
	header('Location:user.php' . $_POST['user_id']);
}

//Code to get list of supervisors to choose from.
$supervisors = user_functions::get_supervisors($db);
$supervisors_html = "<select name='supervisor_id' id='supervisors_input'>";
$supervisors_html .= "<option value='-1'></option>";
foreach ($supervisors as $supervisor) {
	$supervisor_id = $supervisor['id'];
	$supervisor_fullname = $supervisor['full_name'];
	$supervisor_username = $supervisor['username'];
	if ($user->get_supervisor_id() == $supervisor_id) {
		$supervisors_html .= "<option value='" . $supervisor_id . "' selected='selected'>";
		$supervisors_html .= $supervisor_username . " - " . $supervisor_fullname . "</option>";
	}
	else {
		$supervisors_html .= "<option value='" . $supervisor_id . "'>";
		$supervisors_html .= $supervisor_username . " - " . $supervisor_fullname . "</option>";
	}
}
$supervisors_html .= "</select>";

require_once 'includes/header.inc.php';

?>

<form class='form-horizontal' method='post' action='<?php echo $_SERVER['PHP_SELF'] . "?user_id=" . $user_id; ?>'
	name='form'>
	<input type='hidden' name='user_id' value='<?php echo $user_id; ?>'>
	<fieldset>
		<legend>Edit User</legend>
		<div class='control-group'>
			<label class='control-label' for='username_input'>Username:</label>
				<div class='controls'>
					<label><?php echo $user->get_username(); ?></label>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='admin_input'>Is Administrator:</label>
			<div class='controls'>
				<input type='checkbox' name='is_admin' id='is_admin_input'
					<?php if ($user->is_admin()) { echo "checked='checked'"; } ?>>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='is_supervisor_input'>Is Supervisor</label>
			<div class='controls'>
				<input type='checkbox' name='is_supervisor' id='is_supervisor_input'
					onClick='enable_supervisors();' <?php if ($user->is_supervisor()) { echo "checked='checked'"; } ?>>
			</div>
		</div>
		<div class='control-group'>
			<label class='control-label' for='supervisor_input'>Supervisor:</label>
			<div class='controls'>
				<?php echo $supervisors_html; ?>
			</div>
		</div>
		<div class='control-group'>
			<div class='controls'>
				<input class='btn btn-primary' type='submit' name='edit_user'
					value='Edit User'> 
				<input class='btn btn-danger' type='submit' name='delete_user' value='Delete User'
				onClick='return (confirm_disable_user());'>

			<input class='btn btn-warning' type='submit'
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
require_once 'includes/footer.inc.php';
?>
