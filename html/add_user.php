<?php
require_once 'includes/main.inc.php';

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
$supervisors_html = "<select class='custom-select' name='supervisor_id' id='supervisors_input'>";
$supervisors_html .= "<option></option>";
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

require_once 'includes/header.inc.php';
?>

<h3>Add User</h3>
<hr>
<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>' name='form'>
<div class='card'>
<div class='card-header'>User Information</div>
<div class='card-body'>
<br>
<div class='col-sm-8 col-md-8 col-lg-8 col-xl-8'>
	<div class='form-group row'>
		<label class='col-sm-4 col-form-label' for='username_input'>Username:</label>
		<div class='col-sm-8'>
		<input class='form-control' type='text' name='new_username' id='username_input'
			value='<?php if (isset($_POST['new_username'])) { echo $_POST['new_username']; } ?>'>
		</div>
	</div>
	<div class='form-group row'>
		<div class='col-sm-8 offset-sm-4'>
		<div class='form-check'>
		<input class='form-check-input' type='checkbox' name='is_admin' id='is_admin_input'
			<?php if (isset($_POST['is_admin'])) { echo "checked='checked'"; } ?>>
		<label class='form-check-label' for='is_admin_input'>Is Administrator</label>
		</div>
		</div>
	</div>
	<div class='form-group row'>
		<div class='col-sm-8 offset-sm-4'>
		<div class='form-check'>
		<input class='form-check-input' type='checkbox' name='is_supervisor' id='is_supervisor_input'
			onClick='enable_supervisors();' <?php if (isset($_POST['is_supervisor'])) { echo "checked='checked'"; } ?>>
		<label class='form-check-label' for='is_supervisor_input'>Is Supervisor</label>
		</div>
		</div>
	</div>
	<div class='form-group row'>
		<label class='col-sm-4 col-form-label' for='supervisor_input'>Supervisor:</label>
		<div class='col-sm-8'>
			<?php echo $supervisors_html; ?>
		</div>
	</div>
</div>
</div>
</div>
<br>
<div class='card'>
<div class='card-header'>Default Project Billing</div>
<div class='card-body'> 
	<div class='col-sm-8 col-md-8 col-lg-8 col-xl-8'>
		<nav>
			<div class='nav nav-tabs' role='tablist'>
				<a class='nav-item nav-link active' data-toggle='tab' data-target='#nav-cfop' type='button'>CFOP</a>
				<a class='nav-item nav-link' data-toggle='tab' data-target='#nav-custom' type='button'>Custom Billing</a>
				<a class='nav-item nav-link' data-toggle='tab' data-target='#nav-nobill' type='button'>Do Not Bill</a>
			</div>
		</nav>
		<div class='tab-content'>
		<!--------------------------------CFOP-------------------------->
			<div class='tab-pane fade show active' id='nav-cfop' role='tabpanel'>
				<br>	
				<div class='form-group row'>
	                        	<label class='col-sm-3 col-form-label' for='cfop_input'>CFOP:</label>
					<div class='col-sm-1'>
                	                <input class='form-control' type='text' name='cfop_1' id='cfop_input'
                        	                maxlength='1' onKeyUp='cfop_advance_1()'
                                	        value='<?php if (isset($_POST['cfop_1'])) { echo $_POST['cfop_1']; } ?>'>
					</div>
				-
				<div class='col-sm-2'>
                                <input class='form-control' type='text' name='cfop_2'
                                        id='cfop_input' maxlength='6' onKeyUp='cfop_advance_2()'
                                        value='<?php if (isset($_POST['cfop_2'])) { echo $_POST['cfop_2']; } ?>'>
				</div>
				-
				<div class='col-sm-2'>
                                <input class='form-control' type='text' name='cfop_3'
                                        id='cfop_input' maxlength='6' onKeyUp='cfop_advance_3()'
                                        value='<?php if (isset($_POST['cfop_3'])) { echo $_POST['cfop_3']; } ?>'>
				</div>
				-
				<div class='col-sm-2'>
                                <input class='form-control' type='text' name='cfop_4'
                                        id='cfop_input' maxlength='6'
                                        value='<?php if (isset($_POST['cfop_4'])) { echo $_POST['cfop_4']; } ?>'>
				</div>
				</div>
                		<div class='form-group row'>
		                        <label class='col-sm-3 col-form-label' for='activity_input'>Activity Code (optional):</label>
					<div class='col-sm-2'>
                		                <input class='form-control' type='text' name='activity' maxlength='6'
                                	        id='activity_input' value='<?php if (isset($_POST['activity'])) { echo $_POST['activity']; } ?>'>
		                	</div>
				</div>
        	        	<div class='form-group row'>
					<div class='col-sm-9 offset-sm-3'>
					<div clas='form-check'>
                                		<input class='form-check-input' type='checkbox' name='hide_cfop' id='hide_cfop_input' <?php if (isset($_POST['hide_cfop'])) { echo "checked='checked'"; } ?>>
						<label class='form-check-label' for='hide_cfop_input'>Hide CFOP From User</label>
					</div>
					</div>
                		</div>
			</div>
		<!-----------------Custom Billing------------------->

			<div class='tab-pane fade' id='nav-custom' role='tabpanel'>
				<br>
				<div class='form-group'>
					<label class='col-form-label' style='min-width: 200px' for='custom_bill_description'>Custom Bill Description: &nbsp;
						<br>(e.g. Check, Personal Credit Card, Government Credit Card) &nbsp;
					</label>
					<textarea class='form-control' rows='5' cols='80' id='custom_bill_description' 
						name='custom_bill_description'><?php if (isset($_POST['custom_bill_description'])) { echo $_POST['custom_bill_description']; } ?></textarea>

				</div>
			</div>	
		<!------------------Do Not Bill----------------->
			<div class='tab-pane fade' id='nav-nobill' role='tabpanel'>
				<br>
		                <div class='form-group row'>
					<div class='col-sm-9 offset-sm-3'>
					<div class='form-check'>
                		                <input class='form-check-input' type='checkbox' id='bill_project_input' name='bill_project'
                                	        onClick='enable_project_bill();' <?php if (isset($_POST['bill_project'])) { echo "checked='checked'"; } ?>>
						<label class='form-check-label' style='min-width: 200px' for='bill_project_input'>Do not bill default project: &nbsp</label>
					</div>
					</div>
				</div>

			</div>
	

                </div>
</div>
</div>
</div>	
<br>
	<div class='form-group row'>
		<div class='col-sm-12'>
			<input class='btn btn-primary' type='submit' name='add_user' value='Add User'>&nbsp;
			<input class='btn btn-warning' type='submit' name='cancel_user' value='Cancel'>
		</div>
	</div>
</form>
<?php
if (isset($message)) { echo $message; }

?>
</div>

<?php 
require_once 'includes/footer.inc.php';
?>

<script type="text/javascript">
$(document).ready(function() { 
	enable_supervisors();
	$('#supervisors_input').select2({
		'placeholder': "Select a Supervisor"
	});
});
</script>

