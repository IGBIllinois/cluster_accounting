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

$year = date('Y');
$month = date('m');
$start_date = date('Ym') . "01";

if (isset($_GET['month']) && isset($_GET['year'])) {
	$year = $_GET['year'];
	$month = $_GET['month'];
	$start_date = $year . $month . "01";
}

$user = new user($db,$ldap,$user_id);

if (isset($_POST['email_bill'])) {
	try {
		$email_result = $user->email_bill(settings::get_admin_email(),$year,$month,settings::get_website_url());
		$message = "<div class='alert alert-success'>Email Bill - User " . $user->get_username() . " successfully sent to " . $user->get_email() . "</div>";
	}
	catch (\Exception $e) {
		$message = "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
	}
}

$end_date = date('Ymd',strtotime('-1 second',strtotime('+1 month',strtotime($start_date))));
$month_name = date('F',strtotime($start_date));

//list of users to select from
$user_list = array();
if ($login_user->is_supervisor()) {
	$user_list = $login_user->get_supervising_users();
}
if ($login_user->is_admin()) {
	$user_list = user_functions::get_users($db);
}
$user_list_html = "";
if (count($user_list)) {
	$user_list_html = "<div class='col-auto'><label class='form-label'>User: </label></div>";
	$user_list_html .= "<div class='col-sm-3'><select class='form-select' name='user_id' id='user_input' data-placeholder='Select a User'>";
	if ((!isset($_GET['user_id'])) || ($_GET['user_id'] == $login_user->get_user_id())) {
                $user_list_html .= "<option value='" . $login_user->get_user_id(). "' selected='selected'>";
                $user_list_html .= $login_user->get_username() . "</option>";
        }
        else {
                $user_list_html .= "<option value='" . $login_user->get_user_id() . "'>";
                $user_list_html .= $login_user->get_username() . "</option>";
        }

	foreach ($user_list as $user) {

		if ($user['user_id'] == $user_id) {
			$user_list_html .= "<option value='" . $user['user_id'] . "' selected='true'>" . $user['user_name'] . "</option>";
		}
		else {
			$user_list_html .= "<option value='" . $user['user_id'] . "'>" . $user['user_name'] . "</option>";
		}

	}
	$user_list_html .= "</select></div>";
}

//////Year////////
$min_year = job_bill::get_minimal_year($db);
$year_html = "";
for ($i=$min_year; $i<=date("Y");$i++) {
	if ($i == $year) {
		$year_html .= "<option value='$i' selected='true'>$i</option>";
	}
	else { $year_html .= "<option value='$i'>$i</option>";
	}
}

///////Month///////
$month_html = "";
for ($i=1;$i<=12;$i++) {
        if ($i == $month) { $month_html .= "<option value='$i' selected='true'>" . $i . " - " . date('F', mktime(0, 0, 0, $i, 10)) . "</option>"; }
        else { $month_html .= "<option value='$i'>" . $i . " - " . date('F', mktime(0, 0, 0, $i, 10)) . "</option>"; }
}

$user = new user($db,$ldap,$user_id);
$jobs = $user->get_jobs_summary($month,$year);
$jobs_html = "";
if (count($jobs) > 0) {
	foreach($jobs as $job) {
		$jobs_html .= "<tr>";
		$jobs_html .= "<td>" . $job['queue'] . "</td>";
		$jobs_html .= "<td>" . $job['project'] . "</td>";
		$jobs_html .= "<td>$" . number_format($job['total_cost'],2) . "</td>";
		$jobs_html .= "<td>$" . number_format($job['billed_cost'],2) . "</td>";
		if (($login_user->is_admin()) || (!$job['cfop_restricted'])) {
			$jobs_html .= "<td>" . $job['cfop'] . "</td>";
			$jobs_html .= "<td>" . $job['activity_code'] . "</td>";
		}
		else {
			$jobs_html .= "<td colspan='2'>RESTRICTED</td>";
		}
		$jobs_html .= "</tr>";
	}
}
else {
	$jobs_html = "<tr><td colspan='6'>No Jobs</td></tr>";
}

$data_usage = $user->get_data_summary($month,$year);
$data_html = "";
if (count($data_usage)) {
	foreach ($data_usage as $value) {
		$data_html .= "<tr>";
		$data_html .= "<td>" . $value['directory'] . "</td>";
		$data_html .= "<td>" . $value['project'] . "</td>";
		$data_html .= "<td>" . $value['terabytes'] . "</td>";
		$data_html .= "<td>$" . number_format($value['total_cost'],2) . "</td>";
		$data_html .= "<td>$" . number_format($value['billed_cost'],2) . "</td>";
		if ($login_user->is_admin() || (!$value['cfop_restricted'])) {
			$data_html .= "<td>".  $value['cfop'] . "</td>";
			$data_html .= "<td>" . $value['activity_code'] . "</td>";
		}
		else {
			$data_html .= "<td colspan='2'>RESTRICTED</td>";
		}
		$data_html .= "</tr>";
	}
}
else {
		$data_html .= "<tr><td colspan='7'>No Data Usage or not calculated</td></tr>";
}

$get_vars = array('user_id'=>$user_id,
	'month'=>$month,
	'year'=>$year);

$self_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($get_vars);

require_once 'includes/header.inc.php';

?>
<h3>User Bill - <?php echo $month_name . " " . $year; ?></h3>
<hr>
<form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get'>
	<div class='row mb-3'>
		<?php if ($login_user->is_admin() || $login_user->is_supervisor()) {
				echo $user_list_html;
			} 
		?>
		<div class='col-auto'>
			<label class='form-label' for='month'>Month: </label>
		</div>
		<div class='col-sm-3'>
			<select class='form-select' name='month' id='month'>
			<?php echo $month_html; ?>
			</select>
		</div>
		<div class='col-auto'>
			<label class='form-label' for='year' >Year: </label>
		</div>
		<div class='col-sm-2'>
			<select class='form-select' name='year' id='year'>
			<?php echo $year_html; ?>
			</select>
		</div>
		<div class='col'>
			<input class='btn btn-primary' type='submit' value='Get Bill'>
		</div>
	</div>
</form>
<br>
<table class='table table-sm table-striped table-bordered'>

	<tr>
		<td>Name:</td>
		<td><?php echo $user->get_full_name(); ?></td>
	</tr>
	<tr>
		<td>Username:</td>
		<td><?php echo $user->get_username(); ?></td>
	</tr>
	<tr>
		<td>Supervisor:</td>
		<td><?php echo $user->get_supervisor_name(); ?></td>
	<tr>
		<td>Billing Dates:</td>
		<td><?php echo \IGBIllinois\Helper\date_helper::get_pretty_date($start_date); ?> - <?php echo \IGBIllinois\Helper\date_helper::get_pretty_date($end_date); ?>
		</td>
	</tr>
</table>

<h4>Cluster Usage</h4>
<table class='table table-sm table-striped table-bordered'>
	<thead>
		<tr>
			<th>Queue</th>
			<th>Project</th>
			<th>Cost</th>
			<th>Billed Amount</th>
			<th>CFOP</th>
			<th>Activity Code</th>
		</tr>
	</thead>
	<?php echo $jobs_html; ?>
</table>
<h4>Data Usage</h4>
<table class='table table-sm table-striped table-bordered'>
	<thead>
		<tr>
			<th>Directory</th>
			<th>Project</th>
			<th>Terabytes</th>
			<th>Cost</th>
			<th>Billed Amount</th>
			<th>CFOP</th>
			<th>Activity Code</th>

		</tr>
	</thead>
	<?php echo $data_html; ?>
</table>
<form method='post' action='report.php'>
<input type='hidden' name='start_date' value='<?php echo $start_date; ?>'>
<input type='hidden' name='end_date' value='<?php echo $end_date; ?>'>
<input type='hidden' name='user_id' value='<?php echo $user_id;?>'>
<div class='row g-3'>
        <div class='col-sm-2'>
        <select class='form-select' name='report_type'>
                <option value='xlsx'>Excel</option>
                <option value='csv'>CSV</option>
        </select>
        </div>
        &nbsp;
        <div class='col-sm-2'>
        <input class='btn btn-primary' type='submit'
                name='user_job_report' value='Download Cluster Usage Report'>
        </div>
</div>
</form>

<br>
<form method='post' action='<?php echo $self_url; ?>'>
	<input class='btn btn-primary' type='submit'
		name='email_bill' value='Email Bill to User'>
</form>
<br>
<?php 
if (isset($message)) { echo $message; } ?>

</div>
<script type='text/javascript'>
$(document).ready(function() {
        $('#user_input').select2({
                placeholder: $( this ).data( 'placeholder' )
        });
});

</script>

<?php require_once 'includes/footer.inc.php'; ?>

