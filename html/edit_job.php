<?php
require_once 'includes/main.inc.php';

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
if (isset($_POST['update_project'])) {
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
$edit_cfop_html = "<option value='0'>NEW CFOP</option>";
foreach ($all_cfops as $cfop) {
	$edit_cfop_html .= "<option value='" . $cfop['cfop_id'] . "'>";
	$edit_cfop_html .= $cfop['cfop_value'] . " " . $cfop['cfop_activity'] . "</option>";

}

require_once 'includes/header.inc.php';
?>
<h3>
	Job #
	<?php echo $job->get_full_job_number(); ?>
	Details
</h3>
<div class='row'>
<div class='col-sm-6 col-md-6 col-lg-6 col-xl-6'>
<form method='post' name='form' action='<?php echo $_SERVER['PHP_SELF'] . "?job=" . $job->get_full_job_number(); ?>'>
<table class='table table-bordered table-sm table-striped'>
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
                <td><select class='form-select' name='billed_project'><?php echo $project_html; ?></td>
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
		<td>
			<div class='input-group'>
				<span class='input-group-text'>$</span>
				<input class='form-control' type='text' name='billed_cost' value='<?php echo $billed_cost; ?>'>
			</div>
		</td>
		<td><input class='btn btn-primary btn-small' type='submit' name='update_cost' value='Update'></td>
        </tr>
</table>
</div>
</div>

<div class='row'>
	<div class='col'>
		<input class='btn btn-primary' type='submit' name='cancel' value='Back'>
	</div>
</div>
</form>
<?php echo functions::output_message($messages); ?>
</div>
<?php 
require_once 'includes/footer.inc.php'; ?>
