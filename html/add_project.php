<?php
require_once 'includes/main.inc.php';

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

require_once 'includes/header.inc.php';
?>
<h3>Add Project</h3>
<hr>
<div class='col-sm-6 col-md-6 col-lg-6 col-xl-6'>
<form class='form' name='form' method='post'
	action='<?php echo $_SERVER['PHP_SELF']; ?>'>
		<div class='form-group row'>
			<label class='col-sm-4 col-form-label' for='name_input'>Project Name: </label>
			<div class='col-sm-8'>
				<input class='form-control' type='text' name='name' id='name_input'
					value='<?php if (isset($_POST['name'])) { echo $_POST['name']; } ?>' autocapitalize='none'>
			</div>
		</div>
		<div class='form-group row'>
			<label class='col-sm-4 col-form-label' for='owner_input'>Owner: </label>
			<div class='col-sm-8'>
				<?php echo $owner_html; ?>
			</div>
		</div>
		<div class='form-group row'>
			<label class='col-sm-4 col-form-label' for='ldap_group_input'>LDAP Group: </label>
			<div class='col-sm-8'>
				<input class='form-control' type='text' name='ldap_group' id='ldap_group_input'
					value='<?php if(isset($_POST['ldap_group'])) { echo $_POST['ldap_group']; } ?>' autocapitalize='none'>
			</div>
		</div>
		<div class='form-group row'>
			<label class='col-sm-4 col-form-label' for='description_input'>Description: </label>
			<div class='col-sm-8'>
				<input class='form-control' type='text' name='description' id='description_input'
					value='<?php if(isset($_POST['description'])) { echo $_POST['description']; } ?>'>
			</div>
		</div>
		<br>
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

		<br>
		<div class='form-group row'>
			<div class='col-sm-8'>
				<input class='btn btn-primary' type='submit' name='add_project'
					value='Add Project'> <input class='btn btn-warning' type='submit'
					name='cancel_project' value='Cancel'>
			</div>
		</div>
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
$(document).ready(function() {
        $('#owner_input').select2({
                'placeholder': "Select a Owner"
        });
});

</script>

