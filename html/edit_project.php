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
		$previous_cfops_html .= "<td><i class='fas fa-check'></i></td>";
	}
	else {
		$previous_cfops_html .= "<td><i class='fas fa-times'></i></td>";
                                }

	$previous_cfops_html .= "<td>" . $cfops['cfop_time_created'] . "</td>";


}
$users = user_functions::get_users($db);
$owner_html = "";

if ($project->get_default()) {
	$owner_html = "<select class='custom-select' name='owner' id='owner_input' readonly='readonly'>";
}
else {
	$owner_html = "<select class='custom-select' name='owner' id='owner_input'>";
}
$owner_html .= "<option></option>";
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
<h3>Project - <?php echo $project->get_name(); ?></h3>
<hr>
<table class='table table-bordered table-striped table-sm'>
	<thead>
		<tr><th>Project Members</th></tr>
	</thead>
	<?php echo $group_members_html; ?>
</table>

<div class='card'>
<div class='card-header'>Edit Project</div>
<div class='col-sm-8 col-md-8 col-lg-8 col-xl-8'>
<br>
<form name='form' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>?project_id=<?php echo $project->get_project_id(); ?>'>
	<input type='hidden' name='project_id' value='<?php echo $project->get_project_id(); ?>'>
		<div class='form-group row'>
			<label class='col-sm-4 form-label' for='ldap_group_input'>LDAP Group: </label>
			<div class='col-sm-8'>
				<input class='form-control' type='text' name='ldap_group' id='ldap_group_input'
				<?php if ($project->get_default()) { echo "readonly='readonly'"; } ?>
					value='<?php echo $project->get_ldap_group(); ?>'>
			</div>
		</div>
		<div class='form-group row'>
			<label class='col-sm-4 form-label' for='owner_input'>Owner: </label>
			<div class='col-sm-8'>
				<?php echo $owner_html; ?>
			</div>
		</div>
		<div class='form-group row'>
			<label class='col-sm-4 form-label' for='description_input'>Description: </label>
			<div class='col-sm-8'>
				<input class='form-control' type='text' name='description' id='description_input'
				<?php if ($project->get_default()) { echo "readonly='readonly'"; } ?>
					value='<?php echo $project->get_description(); ?>'>
			</div>
		</div>

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
</form>
<br>
</div>
</div>
<br>
<div class='card'>
<div class='card-header'>Previous CFOPs</div>
<div class='card-body'>
<table class='table table-striped table-sm table-bordered'>
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
</div>
</div>
<br>
<?php if (isset($_SERVER['HTTP_REFERER'])) {
        echo "<a href='" . $_SERVER['HTTP_REFERER'] . "' class='btn btn-primary'>Back</a>";

}

?>


<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}

require_once 'includes/footer.inc.php';
?>

<script type='text/javascript'>
$(document).ready(function() {
	enable_project_bill();
	$('#owner_input').select2({
		'placeholder': "Select a Owner"
	});
});
</script>

