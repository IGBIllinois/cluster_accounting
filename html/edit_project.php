<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}
$message = "";
if (isset($_POST['delete_project'])) {
	$project_id = $_POST['project_id'];
	$project = new project($db,$project_id);
	$result = $project->disable();
	if ($result['RESULT']) {
		$log->send_log($result['MESSAGE']);
		header('Location: projects.php');	
	}
	$message = $result['MESSAGE'];
}
elseif (isset($_POST['update_bill'])) {
	$project_id = $_POST['project_id'];
	$month = $_POST['month'];
	$year = $_POST['year'];
	$job_result = job_bill::update_bill($db,$project_id,$month,$year);
	$message = $job_result['MESSAGE'];
	$data_result = data_dir::update_bill($db,$project_id,$month,$year);
	$message .= $data_result['MESSAGE'];

}
elseif (isset($_POST['edit_project'])) {
	$_POST = array_map('trim',$_POST);

	$project = new project($db,$_POST['project_id']);
	$hide_cfop = 0;
        $cfop = $_POST['cfop_1'] . "-" . $_POST['cfop_2'] . "-" . $_POST['cfop_3'] . "-" . $_POST['cfop_4'];
	$owner_id = 0;
	if (isset($_POST['owner_id'])) {
		$owner_id = $_POST['owner_id'];
	}
	switch ($_POST['cfop_billtype']) {
                case 'cfop':
                        if (isset($_POST['hide_cfop'])) {
                                $hide_cfop = 1;
                        }
                        $_POST['custom_bill_description'] = "";
                        break;
                case 'custom':
                        $_POST['cfop_1'] = "";
                        $_POST['cfop_2'] = "";
                        $_POST['cfop_3'] = "";
                        $_POST['cfop_4'] = "";
                        $_POST['activity'] = "";
                        unset($_POST['hide_cfop']);
                        break;

                case 'no_bill':
                        $_POST['cfop_1'] = "";
                        $_POST['cfop_2'] = "";
                        $_POST['cfop_3'] = "";
                        $_POST['cfop_4'] = "";
                        $_POST['activity'] = "";
                        unset($_POST['hide_cfop']);
                        $_POST['custom_bill_description'] = "";
                        break;





        }

	$result = $project->edit($ldap,$_POST['ldap_group'],$_POST['description'],
			$_POST['cfop_billtype'],$owner_id,$cfop,$_POST['activity'],$hide_cfop,$_POST['custom_bill_description']);
	$message = $result['MESSAGE'];

}

if (isset($_GET['project_id']) && is_numeric($_GET['project_id'])) {
        $project_id = $_GET['project_id'];
        $project = new project($db,$project_id);
	if (!isset($_POST['cfop_billtype'])) {
                $_POST['cfop_billtype'] = $project->get_billtype();
        } 

	if (!isset($_POST['custom_bill_description'])) {
                $_POST['custom_bill_description'] = $project->get_custom_bill_description();
        }

}
else {
	exit;
}

$previous_cfops = $project->get_all_cfops();
$previous_cfops_html = "";
foreach ($previous_cfops as $cfops) {
	$previous_cfops_html .= "<tr><td>" . $cfops['cfop_billtype'] . "</td>";
	$previous_cfops_html .= "<td>" . $cfops['cfop_value'] . "</td>";
	$previous_cfops_html .= "<td>" . $cfops['cfop_activity'] . "</td>";
	$previous_cfops_html .= "<td>" . $cfops['cfop_custom_description'] . "</td>";
	$previous_cfops_html .= "<td>" . $cfops['cfop_time_created'] . "</td>";


}
$users = user_functions::get_users($db);
$owner_html = "";

if ($project->get_default()) {
	$owner_html = "<select class='form-select' name='owner_id' id='owner_input' disabled>";
}
else {
	$owner_html = "<select class='form-select' name='owner_id' id='owner_input'>";
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
	$group_members_html .= "<tr><td><a href='user.php?username=" . $member . "'>" . $member . "</a></td></tr>";
}

$directories = $project->get_directories();
$directories_html = "";
foreach ($directories as $directory) {
	$directories_html .= "<tr><td><a href='data_dir.php?data_dir_id=" . $directory['data_dir_id'] . "'>" . $directory['data_dir_path'] . "</a></td></tr>";
}

$selected_month = new DateTime('now');
$month_name = $selected_month->format('F');
$month = $selected_month->format('m');
$year = $selected_month->format('Y');

//////Year////////
$min_year = job_bill::get_minimal_year($db);
$year_html = "<div class='col-sm-2'><select class='form-select' name='year'>";
for ($i=$min_year; $i<=date("Y");$i++) {
        if ($i == $year) { $year_html .= "<option value='" . $i . "' selected='true'>" . $i . "</option>"; }
        else { $year_html .= "<option value='" . $i . "'>" . $i . "</option>"; }
}
$year_html .= "</select></div>";

///////Month///////
$month_html = "<div class='col-sm-3'><select class='form-select' name='month'>";
for ($i=1;$i<=12;$i++) {
        if ($i == $month) { $month_html .= "<option value='$i' selected='true'>" . $i . " - " . date('F', mktime(0, 0, 0, $i, 10)) . "</option>"; }
        else { $month_html .= "<option value='$i'>" . $i . " - " . date('F', mktime(0, 0, 0, $i, 10)) . "</option>"; }
}
$month_html .= "</select></div>";

$previous_bill_html = $year_html . $month_html;

require_once 'includes/header.inc.php';
?>
<h3>Project - <?php echo $project->get_name(); ?></h3>
<hr>
<div class='card'>
<div class='card-header'>Project Members</div>
<div class='col-sm-12 col-md-12 col-lg-12 col-xl-12'>
<br>
<table class='table table-bordered table-striped table-sm'>
	<tbody>
	<?php echo $group_members_html; ?>
	</tbody>
</table>
</div>
</div>
<br>
<div class='card'>
<div class='card-header'>Project Directories</div>
<div class='col-sm-12 col-md-12 col-lg-12 col-xl-12'>
<br>
<table class='table table-bordered table-striped table-sm'>
        <tbody>
        <?php echo $directories_html; ?>
        </tbody>
</table>
</div>
</div>

<br>
<form name='form' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>?project_id=<?php echo $project->get_project_id(); ?>'>
<div class='card'>
<div class='card-header'>Edit Project</div>
<div class='col-sm-12 col-md-12 col-lg-12 col-xl-12'>
<div class='card-body'>
<br>
	<input type='hidden' name='project_id' value='<?php echo $project->get_project_id(); ?>'>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='ldap_group_input'>LDAP Group: </label>
		<div class='col-sm-2'>
			<input class='form-control' type='text' name='ldap_group' id='ldap_group_input'
			<?php if ($project->get_default()) { echo "readonly='readonly'"; } ?>
				value='<?php echo $project->get_ldap_group(); ?>'>
		</div>
	</div>
	<div class='mb-3 row'>
		<label class='col-sm-2' for='owner_input'>Owner: </label>
		<div class='col-sm-2'>
			<?php echo $owner_html; ?>
		</div>
	</div>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='description_input'>Description: </label>
		<div class='col-sm-4'>
			<input class='form-control' type='text' name='description' id='description_input'
			<?php if ($project->get_default()) { echo "readonly='readonly'"; } ?>
				value='<?php echo $project->get_description(); ?>'>
		</div>
	</div>

	<nav>
		<div class='nav nav-tabs' role='tablist' id='billing_tab'>
			<a class='nav-link active' data-bs-toggle='tab' data-bs-target='#nav-cfop' type='button'>CFOP</a>
			<a class='nav-link' data-bs-toggle='tab' data-bs-target='#nav-custom' type='button'>Custom Billing</a>
			<a class='nav-link' data-bs-toggle='tab' data-bs-target='#nav-nobill' type='button'>Do Not Bill</a>
		</div>
	</nav>
	<div class='tab-content'>
	<!--------------------------------CFOP-------------------------->
		<div class='tab-pane fade show active' id='nav-cfop' role='tabpanel'>
			<br>
			<div class='mb-3 row'>
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
			<div class='mb-3 row'>
				<label class='col-sm-3 col-form-label' for='activity_input'>Activity Code (optional):</label>
				<div class='col-sm-2'>
					<input class='form-control' type='text' name='activity' maxlength='6'
					id='activity_input' value='<?php if (isset($_POST['activity'])) { echo $_POST['activity']; } ?>'>
				</div>
			</div>
			<div class='mb-3 row'>
				<div class='form-check form-switch offset-md-3'>
					<input class='form-check-input' type='checkbox' name='hide_cfop' id='hide_cfop_input' <?php if (isset($_POST['hide_cfop'])) { echo "checked='checked'"; } ?>>
					<label class='form-check-label' for='hide_cfop_input'>Hide CFOP From User</label>
				</div>
			</div>
		</div>
                <!-----------------Custom Billing------------------->
		<div class='tab-pane fade' id='nav-custom' role='tabpanel'>
			<br>
			<div class='mb-3'>
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
			<div class='mb-3 row'>
				<div class='col-sm-9 offset-sm-3'>
				<p>Selecting 'Do Not Bill' will not enabling billing for this user</p>
				</div>
			</div>

		</div>
	</div>
</div>
</div>
</div>
<br>
<div class='mb-3 row'>
	<div class='col-sm-8'>
		<input type='hidden' name='cfop_billtype' id='cfop_billtype' value='<?php if (isset($_POST['cfop_billtype'])) { echo $_POST['cfop_billtype']; } ?>'>
		<input class='btn btn-primary' type='submit' name='edit_project' value='Edit Project'>
		<?php if (!$project->get_default()) { echo "<input class='btn btn-danger' type='submit' name='delete_project' value='Delete Project' onClick='return confirm_delete_project()'>"; } ?>
	</div>
</div>
</form>
<br>
<br>
<div class='card'>
<div class='card-header'>Previous Billings</div>
<div class='card-body'>
<table class='table table-striped table-sm table-bordered'>
        <thead>
                <tr>
			<th>Bill Type</th>
                        <th>CFOP</th>
                        <th>Activity Code</th>
			<th>Custom Bill Description</th>
                        <th>Date Added</th>
                </tr>
        </thead>
        <?php echo $previous_cfops_html; ?>


</table>
</div>
</div>
<br>
<div class='card'>
<div class='card-header'>Update Previous Bill</div>
<div class='card-body'>
	<form action='<?php echo $_SERVER['PHP_SELF']; ?>?project_id=<?php echo $project->get_project_id(); ?>' method='post'>
		<input type='hidden' name='project_id' value='<?php echo $project_id; ?>'>
		<div class='row'>
			<?php echo $previous_bill_html; ?>
			<div class='col'>
				<input class='btn btn-primary' type='submit' name='update_bill' value='Update Bill' onClick='return confirm_edit_project_bill()'>	
			</div>
		</div>
	</form>
</div>
</div>
<br>
<?php if (isset($_SERVER['HTTP_REFERER'])) {
        echo "<a href='" . $_SERVER['HTTP_REFERER'] . "' class='btn btn-primary'>Back</a>";

}

?>

<br>
<br>

<?php
if (isset($message)) {
	echo $message;
}
?>
</div>
<script type='text/javascript'>
$(document).ready(function() {
        $('#owner_input').select2({
                placeholder: "Select a Owner"
        });

        set_cfop_billtype_tab();
        set_cfop_billtype_value();
});
</script>
<?php
require_once 'includes/footer.inc.php';
?>

