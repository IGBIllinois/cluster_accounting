<?php
require_once 'includes/main.inc.php';

$user_id = $login_user->get_user_id();
if (isset($_GET['user_id']) && (is_numeric($_GET['user_id']))) {
        $user_id = $_GET['user_id'];
}

if (!$login_user->permission($user_id)) {
        echo "Invalid Permissions";
        exit;
}

$count = 40;
$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start = $_GET['start'];
}


$running_jobs = array();
$running_jobs = job_functions::get_running_jobs($db,$user_id,$start,$count);
$number_jobs = job_functions::get_num_running_jobs($db,$user_id);
$get_array = array('user_id'=>$user_id);


$pages_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($get_array);
$pages_html = html::get_pages_html($pages_url,$number_jobs,$start,$count);

$jobs_html = "";
if (count($running_jobs)) {
        foreach ($running_jobs as $job) {
		$state_html = "";
		switch($job['state']) {
			case 'RUNNING':
				$state_html = "<span class='badge rounded-pill bg-success'>&nbsp;</span>";
				break;
			case 'PENDING':
				$state_html = "<span class='badge rounded-pill bg-info'>&nbsp;</span>";

		}
                $jobs_html .= "<tr>";
                $jobs_html .= "<td>" . $state_html . "</td>";
		$jobs_html .= "<td>" . $job['job_number'] . "</td>";
		$jobs_html .= "<td>" . $job['username'] . "</td>";
		$jobs_html .= "<td>" . $job['job_name'] . "</td>";
		$jobs_html .= "<td>" . $job['project'] . "</td>";
		$jobs_html .= "<td>" . $job['queue'] . "</td>";
                $jobs_html .= "<td>" . $job['elapsed_time'] . "</td>";
                $jobs_html .= "<td>" . $job['cpus'] . "</td>";
                $jobs_html .= "<td>" . $job['mem_reserved'] . "</td>";
                $jobs_html .= "<td>" . $job['gpus'] . "</td>";
                $jobs_html .= "<td>$" . $job['current_cost'] . "</td>";
                $jobs_html .= "</tr>";
        }
}
else {
        $jobs_html = "<tr><td colspan='11'>No Running or Pending Jobs</td></tr>";
}


//list of users to select from
$user_list = array();
if ($login_user->is_admin()) {
	$user_list = user_functions::get_users($db,"","");
}
elseif ($login_user->is_supervisor()) {
	$user_list = $login_user->get_supervising_users();
}
$user_list_html = "";

if (count($user_list) > 1) {
        $user_list_html = "<select class='custom-select' name='user_id' id='user_id_input'>";
	$user_list_html .= "<option></option>";
	if ($login_user->is_admin()) {
		$user_list_html .= "<option value='0'>All Users</option>";
	}
	if ($user_id == $login_user->get_user_id()) {
		$user_list_html .= "<option value='" . $login_user->get_user_id() . "' selected='selected'>";
		$user_list_html .= $login_user->get_username() . "</option>";
	}
	else {
                $user_list_html .= "<option value='" . $login_user->get_user_id() . "'>"; 
                $user_list_html .= $login_user->get_username() . "</option>";
	}
        foreach ($user_list as $user) {

                if ($user['user_id'] == $user_id) {
                        $user_list_html .= "<option value='" . $user['user_id'] . "' selected='selected'>" . $user['user_name'] . "</option>";
                }
                else {
                        $user_list_html .= "<option value='" . $user['user_id'] . "'>" . $user['user_name'] . "</option>";
                }

        }
        $user_list_html .= "</select>";
}

require_once 'includes/header.inc.php';
?>

<h3>Search Jobs</h3>
<div class='row'>
<div class='col-sm-8 col-md-8 col-lg-8 col-xl-8'>
	<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
                <input type='text' name='search' class='form-control' placeholder='Search'
			value='<?php if (isset($_GET['search'])) { echo $_GET['search']; } ?>' autocapitalize='none'>
		<?php
			if ($login_user->is_admin() || $login_user->is_supervisor()) {
				echo $user_list_html;
			}

		?>
                <input type='submit' class='btn btn-primary' value='Search'>
	</form>
</div>
</div>
<br>
<div class='row'>
	<div class='col-sm-4 col-md-5 col-lg-4 col-xl-4'>
	<ul class='list-inline'>
		<li class='list-inline-item'><span class='badge rounded-pill bg-success'>&nbsp;</span> Running Job</li>
		<li class='list-inline-item'><span class='badge rounded-pill bg-info'>&nbsp;</span> Pending Job</li>
	</ul>
	</div> 
</div>
<div class='row'>
<table class='table table-sm table-bordered table-striped'>
        <thead>
                <tr>
			<th>&nbsp;</th>
			<th>Job Number</th>
			<th>Username</th>
			<th>Job Name</th>
			<th>Project</th>
                        <th>Queue</th>
			<th>Elapsed Time</th>
                        <th>Reserved CPUs</th>
			<th>Reserved Mem (GB)</th>
			<th>Reserved GPUs</th>
                        <th>Current Cost</th>

                </tr>
        </thead>
        <?php echo $jobs_html; ?>
</table>
</div>
<div class='row justify-content-center'>
<?php echo $pages_html; ?>
</div>

<script type="text/javascript">

$(document).ready(function() {
        $('#user_id_input').select2({
                placeholder: 'Select a User'
        });
});
</script>

<?php

require_once 'includes/footer.inc.php';
?>
