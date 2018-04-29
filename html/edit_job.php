<?php
require_once 'includes/header.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

if (isset($_GET['job'])) {

	$job = new job($db,$_GET['job']);
	if (!$job->job_exists($_GET['job'])) {
		echo "<h3>Job " . $_GET['job'] . " does not exist</h3>";
		exit;

	}

	$billed_cost = $job->get_billed_cost();
}
else {
        echo "<h3>This job does not exist</h3>";
        exit;
}
$messages = array();
if (isset($_POST)) {
        foreach ($_POST as &$var) {
                $var = trim(rtrim($var));
        }
}
if (isset($_POST['update_job'])) {
	if ($job->get_project()->get_project_id() != $_POST['billed_project']) {
                $result = $job->set_project($_POST['billed_project']);
                array_push($messages,$result);
        }
	
}
elseif (isset($_POST['update_cost'])) {
	$billed_cost = $_POST['billed_cost'];
	if ($billed_cost != $job->get_billed_cost()) {
		$result = $job->set_billed_cost($billed_cost);
		array_push($messages,$result);
	}
}
elseif (isset($_POST['edit_cfop'])) {
	if ($_POST['new_cfop']) {
		$cfop_id = $_POST['new_cfop'];
		$result =$job->set_cfop($cfop_id);
	}
	else {
		$hide_cfop = 0;
        	if (isset($_POST['hide_cfop'])) {
                	$hide_cfop = 1;
	        }

        	$cfop = $_POST['cfop_1'] . "-" . $_POST['cfop_2'] . "-" . $_POST['cfop_3'] . "-" . $_POST['cfop_4'];
		$result = $job->set_new_cfop($cfop,$_POST['activity'],$hide_cfop);
	}
	if ($result['RESULT']) {
		unset($_POST);
	}
	array_push($messages,$result);
}

elseif (isset($_POST['cancel'])) {
	unset($_POST);
	header('Location: job.php?job=' . $job->get_full_job_number());
}

$user = new user($db,$ldap,0,$job->get_username());
$project_html = "";

foreach ($user->get_projects() as $project) {

	if ($project['project_name'] == $job->get_project()->get_name()) {
		$project_html .= "<option selected='selected' value='" . $project['project_id'] . "'>" . $project['project_name'] . "</option>";
	}
	else {
		$project_html .= "<option value='" . $project['project_id'] . "'>" . $project['project_name'] . "</option>";
	}

}

$project = $job->get_project();
$all_cfops = $project->get_all_cfops();
$edit_cfop_html = "<select name='new_cfop' id='new_cfop' onChange='enable_new_cfop();'>";
$edit_cfop_html .= "<option value='0'>NEW CFOP</option>";
foreach ($all_cfops as $cfop) {
	$edit_cfop_html .= "<option value='" . $cfop['cfop_id'] . "'>";
	$edit_cfop_html .= $cfop['cfop_value'] . " " . $cfop['cfop_activity'] . "</option>";

}
$edit_cfop_html .= "</select>";

?>
<h3>
	Job #
	<?php echo $job->get_full_job_number(); ?>
	Details
</h3>
<div class='span8'>
<form method='post' name='form' action='<?php echo $_SERVER['PHP_SELF'] . "?job=" . $job->get_full_job_number(); ?>'>
<table class='table table-bordered table-condensed table-striped'>
        <tr>
                <td>Job Number:</td>
                <td><?php echo $job->get_full_job_number(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Job User:</td>
                <td><?php echo $job->get_username(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Job Name:</td>
                <td><?php echo $job->get_job_name(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Submitted Project:</td>
                <td><?php echo $job->get_submitted_project(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Billed Project:</td>
                <td><select name='billed_project'><?php echo $project_html; ?></td>
		<td><input class='btn btn-primary btn-small' type='submit' name='update_project' value='Update'></td>
        </tr>
        <tr>
                <td>Queue:</td>
                <td><?php echo $job->get_queue_name(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Exit Status:</td>
                <td><?php echo $job->get_exit_status(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Submission Time:</td>
                <td><?php echo $job->get_submission_time(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Start Time:</td>
                <td><?php echo $job->get_start_time(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>End Time:</td>
                <td><?php echo $job->get_end_time(); ?></td>
		<td>&nbsp</td>
        </tr>
	<tr>
		<td>Queued Elapsed Time (H:M:S):</td>
		<td><?php echo $job->get_queued_time_hours(); ?></td>
		<td>&nbsp</td>
	</tr>
        <tr>
                <td>Elapsed Time (H:M:S):</td>
                <td><?php echo $job->get_elapsed_time_hours(); ?></td>
		<td>&nbsp</td>
        </tr>

        <tr>
                <td>CPU Time (H:M:S):</td>
                <td><?php echo $job->get_cpu_time_hours(); ?></td>
		<td>&nbsp</td>
        </tr>
	<tr>
	        <td>Processors:</td>
        	<td><?php echo $job->get_slots(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
		<td>Memory Reserved:</td>
	        <td><?php echo $job->get_reserved_mem_gb(); ?>GB</td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Memory Used:</td>
                <td><?php echo $job->get_used_mem_gb(); ?>GB</td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Virtual Memory Used:</td>
                <td><?php echo $job->get_maxvmem_gb(); ?>GB</td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Cost:</td>
                <td><?php echo $job->get_total_cost(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Amount Billed:</td>
		<td>$<input type='text' name='billed_cost' value='<?php echo $billed_cost; ?>'></td>
		<td><input class='btn btn-primary btn-small' type='submit' name='update_cost' value='Update'></td>
        </tr>
	<tr>
                <td>CFOP:</td>
                <td><?php echo $job->get_cfop(); ?></td>
		<td>&nbsp</td>
        </tr>
        <tr>
                <td>Activity Code:</td>
                <td><?php echo $job->get_activity_code(); ?></td>
		<td>&nbsp</td>
        </tr>
</table>
<h4>Update CFOP</h4>

<?php echo $edit_cfop_html; ?>
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
	<label class='control-label' for='activity_input'>Activity Code(optional):</label>
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
                                <input class='btn btn-primary' type='submit' name='edit_cfop'
                                        value='Edit CFOP'>
                        </div>
                </div>


<div class='row'>
<br>&nbsp
<br>&nbsp
<input class='btn btn-primary' type='submit' name='cancel' value='Back'>
</div>
</form>
<?php echo functions::output_message($messages); ?>
</div>
<?php 
require_once 'includes/footer.inc.php'; ?>
