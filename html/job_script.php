<?php
require_once 'includes/header.inc.php';
$user_id = $login_user->get_user_id();
if (isset($_GET['user_id']) && (is_numeric($_GET['user_id']))) {
        $user_id = $_GET['user_id'];
}

if (!$login_user->permission($user_id)) {
        echo "<div class='alert alert-error'>Invalid Permissions</div>";
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


?>
<h3>Job Script - <?php echo $job->get_full_job_number(); ?></h3>
<?php
	if (!strpos($job->get_job_script(),'module load') && $job->get_job_script_exists()) {
        	echo "<div class='alert alert-error span8'>Please use the module command in your job script.</div>";
	}
?>
<div class='row span11'>
<?php
	echo "<pre class='prettyprint linenums span11'>" . $job->get_job_script() . "</pre>";
?>
</div>
<div class='row span10'>
<a href='job.php?job=<?php echo $job->get_full_job_number(); ?>' class='btn btn-primary'>Back</a>
</div>

<?php

include_once 'includes/footer.inc.php';
?>
