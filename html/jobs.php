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

$start_date = date('Y-m') . "-01";
$end_date = date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime($start_date))));
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
}

$jobs = array();
$search = "";
if (isset($_GET['search'])) {
	$search = $_GET['search'];
}

$completed = -1;
if (isset($_GET['completed'])) {
	$completed = $_GET['completed'];
}
$jobs = job_functions::get_jobs($db,$user_id,$search,$completed,$start_date,$end_date,$start,$count);
$number_jobs = job_functions::get_num_jobs($db,$user_id,$search,$completed,$start_date,$end_date);
$get_array = array('user_id'=>$user_id,
		'search'=>$search,
		'completed'=>$completed,
		'start_date'=>$start_date,
		'end_date'=>$end_date);

$completed_get_array = array('user_id'=>$user_id,
                'search'=>$search,
                'start_date'=>$start_date,
                'end_date'=>$end_date);

$pages_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($get_array);
$pages_html = html::get_pages_html($pages_url,$number_jobs,$start,$count);

$jobs_html = html::get_jobs_rows($jobs);

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
        $user_list_html = "<select class='form-select' name='user_id' id='user_id_input' data-placeholder='Select a User'>";
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
<form method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
	<div class='row'>
		<div class='col-sm-3'>
                <input type='text' name='search' class='form-control' placeholder='Search'
			value='<?php if (isset($_GET['search'])) { echo $_GET['search']; } ?>' autocapitalize='none'>
		</div>
		<?php
			if ($login_user->is_admin() || $login_user->is_supervisor()) {
				echo "<div class='col-sm-2'>" . $user_list_html . "</div>";
			}

		?>
		<div class='col-sm-2'>
		<input class='form-select' type='text' name='start_date' id='start_date' placeholder='Start Date'
			value='<?php if (isset($start_date)) { echo $start_date; } ?>'>
		</div>
		<div class='col-sm-2'>
		<input class='form-select' type='text' name='end_date' id='end_date' placeholder='End Date'
			value='<?php if (isset($end_date)) { echo $end_date; } ?>'>
		</div>
		<div class='col'>
                <input type='submit' class='btn btn-primary' value='Search'>
		</div>
	</div>
</form>

<p>
<div class='row'>
	<div class='col-sm-4 col-md-5 col-lg-4 col-xl-4'>
	<ul class='list-inline'>
		<li class='list-inline-item'><span class='badge rounded-pill bg-success'>&nbsp;</span> Completed Job</li>
		<li class='list-inline-item'><span class='badge rounded-pill bg-danger'>&nbsp;</span> Failed Job</li>
	</ul>
	</div>
	<div class='col d-flex justify-content-end'>
		<div class='btn-group' role='group'>
			<a class='btn btn-primary' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query($completed_get_array); ?>'>All Jobs</a>
			<a class='btn btn-success' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query($completed_get_array) . "&completed=1"; ?>'>Completed Jobs</a>
			<a class='btn btn-danger' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query($completed_get_array) . "&completed=0"; ?>'>Failed Jobs</a>
		</div>
	</div>
</div>
<p>
<div class='row'>
<table class='table table-sm table-bordered table-striped'>
        <thead>
                <tr>
			<th></th>
			<th>Job Number</th>
			<th>Username</th>
                        <th>Job Name</th>
                        <th>Project</th>
                        <th>Queue</th>
                        <th>End Time</th>
                        <th>Total Cost</th>
                        <th>Billed Cost</th>

                </tr>
        </thead>
        <?php echo $jobs_html; ?>
</table>
</div>
<div class='row justify-content-center'>
<?php echo $pages_html; ?>
</div>
<form method='post' action='report.php'>
<input type='hidden' name='search' value='<?php echo $search; ?>'>
<input type='hidden' name='start_date' value='<?php echo $start_date; ?>'> 
<input type='hidden' name='end_date' value='<?php echo $end_date; ?>'> 
<input type='hidden' name='user_id' value='<?php echo $user_id;?>'>
<input type='hidden' name='completed' value='<?php echo $completed; ?>'>
<div class='row g-3'>
	<div class='col-sm-2'>
	<select class='form-select' name='report_type'>
                <option value='xlsx'>Excel</option>
                <option value='csv'>CSV</option>
        </select>
	</div>
	&nbsp;
	<div class='col'>
	<input class='btn btn-primary' type='submit'
                name='job_report' value='Download Detailed Report'>
	</div>
</div>	
</form>

</div>
<script type="text/javascript">
$(function() {
        $( "#start_date" ).datepicker({
                maxDate: "+1w",
                minDate: new Date(2010,1-1,1),
                changeYear: true,
                changeMonth: true,
                dateFormat: "yy-mm-dd",
        });
        $( "#end_date" ).datepicker({
                maxDate: "+1w",
                minDate: new Date(2010,1-1,1),
                changeYear: true,
                changeMonth: true,
                dateFormat: "yy-mm-dd",
        });
});

$(document).ready(function() {
        $('#user_id_input').select2({
		theme: 'bootstrap-5',
                placeholder: $( this ).data( 'placeholder' )
        });
});
</script>

<?php

require_once 'includes/footer.inc.php';
?>
