<?php
require_once 'includes/header.inc.php';

$user_id = $login_user->get_user_id();
if (isset($_GET['user_id']) && (is_numeric($_GET['user_id']))) {
        $user_id = $_GET['user_id'];
}

if (!$login_user->permission($user_id)) {
        echo "Invalid Permissions";
        exit;
}


$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start = $_GET['start'];
}
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
}
else {
        $start_date = date('Y-m') . "-01";
        $end_date = date('Y-m-d',strtotime('-1 second',strtotime('+1 month',strtotime($start_date))));
}

$jobs = array();
$search = "";
if (isset($_GET['search'])) {
	$search = $_GET['search'];
}
	
$jobs = job_functions::get_jobs($db,$user_id,$search,$start_date,$end_date);

$count = 40;
$number_jobs = count($jobs);
$get_array = array('user_id'=>$user_id,
		'search'=>$search,
		'start_date'=>$start_date,
		'end_date'=>$end_date);
$pages_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($get_array);
$pages_html = html::get_pages_html($pages_url,$number_jobs,$start,$count);

$jobs_html = html::get_jobs_rows($jobs,$start,$count);

//list of users to select from
if ($login_user->is_admin()) {
	$user_list = user_functions::get_users($db,"","");
}
elseif ($login_user->is_supervisor()) {
	$user_list = $login_user->get_supervising_users();
}
$user_list_html = "";

if (count($user_list) > 1) {
        $user_list_html = "<select class='input-small' name='user_id'>";
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

?>
 <script>
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

</script>



<h3>Search Jobs</h3>
<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
        <!--<div class='input-append'>-->
                <input type='text' name='search' class='input-long' placeholder='Search'
			value='<?php if (isset($_GET['search'])) { echo $_GET['search']; } ?>'>
		<?php
			if ($login_user->is_admin() || $login_user->is_supervisor()) {
				echo $user_list_html;
			}

		?>
		<input class='input-small' type='text' name='start_date' id='start_date' placeholder='Start Date'
			value='<?php if (isset($start_date)) { echo $start_date; } ?>'>
		<input class='input-small' type='text' name='end_date' id='end_date' placeholder='End Date'
			value='<?php if (isset($end_date)) { echo $end_date; } ?>'>
                <input type='submit' class='btn btn-primary' value='Search'>
        <!--</div>-->
</form>
<table class='table table-condensed table-bordered table-striped'>
        <thead>
                <tr>
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
<form class='form-inline' method='post' action='report.php'>
        <input type='hidden' name='start_date'
                value='<?php echo $start_date; ?>'> <input type='hidden'
                name='end_date' value='<?php echo $end_date; ?>'> <input type='hidden'
                name='user_id' value='<?php echo $user_id;?>'> <select
                name='report_type' class='input-medium'>
                <option value='xls'>Excel 2003</option>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> <input class='btn btn-primary' type='submit'
                name='user_job_report' value='Download Detailed Report'>
</form>

<?php echo $pages_html; ?>

<?php

include_once 'includes/footer.inc.php';
?>
