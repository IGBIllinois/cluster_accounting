<?php
require_once 'includes/main.inc.php';

$user_id = $login_user->get_user_id();
if (isset($_GET['user_id']) && (is_numeric($_GET['user_id']))) {
        $user_id = $_GET['user_id'];
}

if (!$login_user->permission($user_id)) {
        echo "<div class='alert alert-danger'>Invalid Permissions</div>";
        exit;
}

if (isset($_GET['job'])) {

        $job = new job($db,$_GET['job']);
        if (!$job->job_exists($_GET['job'])) {
                echo "<h3>Job " . $_GET['job'] . " does not exist</h3>";
                exit;

        }

}
else {
        echo "<h3>This job does not exist</h3>";
        exit;

}

require_once 'includes/header.inc.php';
?>
<h3>Job Script - <?php echo $job->get_full_job_number(); ?></h3>
<?php
	if (!strpos($job->get_job_script(),'module load') && $job->get_job_script_exists()) {
        	echo "<div class='alert alert-danger'>Please use the module command in your job script.</div>";
	}
?>
<div class='row'>
<div class='col-sm-8 col-md-8 col-lg-8 col-xl-8'>
<?php
	echo "<pre class='prettyprint linenums'>" . $job->get_job_script() . "</pre>";
?>
</div>
</div>

<div class='row'>
<div class='col-sm-8 col-md-8 col-lg-8 col-xl-8'>
<a href='job.php?job=<?php echo $job->get_full_job_number(); ?>' class='btn btn-primary'>Back</a>
</div>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
