<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

$selected_month = new DateTime(date('Y-m-01 00:00:00'));
$end_time = date("Y-m-t H:i:s");
$selected_month_end = DateTime::createFromFormat("Y-m-d H:i:s",$end_time);

if (isset($_GET['year']) && isset($_GET['month'])) {
	$year = $_GET['year'];
	$month = $_GET['month'];
	$selected_month = DateTime::createFromFormat("Y-m-d H:i:s",$year . "-" . $month . "-01 00:00:00");
	$end_time = date("Y-m-t H:i:s",strtotime($year . "-" . $month . "-01 23:59:59"));
        $selected_month_end = DateTime::createFromFormat("Y-m-d H:i:s",$end_time);
}

$month_name = $selected_month->format('F');
$month = $selected_month->format('m');
$year = $selected_month->format('Y');

//////Year////////
$min_year = job_bill::get_minimal_year($db);
$year_html = "<select class='form-select' name='year'>";
for ($i=$min_year; $i<=date("Y");$i++) {
	if ($i == $year) { $year_html .= "<option value='" . $i . "' selected='true'>" . $i . "</option>"; }
	else { $year_html .= "<option value='" . $i . "'>" . $i . "</option>"; }
}
$year_html .= "</select>";

///////Month///////
$month_html = "<select class='form-select' name='month'>";
for ($i=1;$i<=12;$i++) {
	if ($i == $month) { $month_html .= "<option value='$i' selected='true'>" . $i . " - " . date('F', mktime(0, 0, 0, $i, 10)) . "</option>"; }
	else { $month_html .= "<option value='$i'>" . $i . " - " . date('F', mktime(0, 0, 0, $i, 10)) . "</option>"; }
}
$month_html .= "</select>";
$next_month = DateTime::createFromFormat('Y-m',$year . "-" . $month);
$next_month->modify('first day of next month');
$current_month = new DateTime();

$url_navigation = html::get_url_navigation_month($_SERVER['PHP_SELF'],$year,$month);
$jobs = job_functions::get_jobs_bill($db,$month,$year);

$jobs_html = "";
if (count($jobs)) {
	foreach ($jobs as $job) {
		$jobs_html .="<tr><td>" . $job['Username'] . "</td>";
		$jobs_html .= "<td>" . $job['Project'] . "</td>";
		$jobs_html .= "<td>" . $job['Queue'] . "</td>";
		$jobs_html .= "<td>$" . $job['Total Cost'] . "</td>";
		$jobs_html .= "<td>$" . $job['Billed Cost'] . "</td>";
		$jobs_html .= "<td>" . $job['CFOP'] . "</td>";
		$jobs_html .= "<td>" . $job['Activity Code'] ."</td>";
		$jobs_html .= "</tr>";
	}
}
else {
	$jobs_html = "<tr><td colspan='7'>No Job Billing</td></tr>";
}
$stats = new statistics($db);

require_once 'includes/header.inc.php';
?>
<h3>Job Billing Monthly Reports - <?php echo $month_name . " " . $year; ?></h3>
<form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get'>
	<div class='row'>
		<div class='col-auto'>
			<label class='form-label' for='month'>Month:</label>
		</div>
		<div class='col-sm-2'>
			<?php echo $month_html; ?>
		</div>
		<div class='col-auto'>
			<label class='form-label for='year'>Year:</label>
		</div>
		<div class='col-sm-2'>
			<?php echo $year_html; ?>
		</div>
		<div class='col'>
			<button class='btn btn-primary' type='submit' name='selectedDate'>Get Records</button>
		</div>
	</div>
</form>
<p>
<div class='row'>
	<div class='col-sm-2'>
        <a class='btn btn-sm btn-primary' href='<?php echo $url_navigation['back_url']; ?>'>Previous Month</a>
	</div>
	<div class='col'>
        <?php
                if ($next_month > $current_month) {
                        echo "<div class='d-flex justify-content-end'><a class='btn btn-sm btn-primary' onclick='return false;'>Next Month</a></div>";
                }
                else {
                        echo "<div class='d-flex justify-content-end'><a class='btn btn-sm btn-primary' href='" . $url_navigation['forward_url'] . "'>Next Month</a></div>";
                }
        ?>
	</div>
</div>
<p>
<table class='table table-striped table-sm table-bordered'>
	<thead>
		<tr>
			<th>Username</th>
			<th>Project</th>
			<th>Queue</th>
			<th>Total Cost</th>
			<th>Billed Cost</th>
			<th>CFOP</th>
			<th>Activity Code</th>
		</tr>
	</thead>
	<tbody>
	<?php echo $jobs_html; ?>
	<tr>
		<td>Monthly Total Cost:</td>
		<td colspan='7'>$<?php echo $stats->get_job_total_cost($selected_month,$selected_month_end,true); ?>
		</td>
	</tr>
	<tr>
		<td>Monthly Billed Cost:</td>
		<td colspan='7'>$<?php echo $stats->get_job_total_billed_cost($selected_month,$selected_month_end,true); ?>
		</td>
	</tr>
	</tbody>
</table>
<br>

<form action='report.php' method='post'>
	<input type='hidden' name='month' value='<?php echo $month; ?>'>
	<input type='hidden' name='year' value='<?php echo $year; ?>'>
	<div class='row'>
		<div class='col-sm-2'>
			<select name='report_type' class='form-select'>
			<option value='xlsx'>Excel</option>
			<option value='csv'>CSV</option>
			</select>
		</div>
		<div class='col'>
			<input class='btn btn-primary' type='submit' name='create_job_report' value='Download Full Report'>
			<input class='btn btn-primary' type='submit' name='create_job_fbs_report' value='Download FBS Report'>
			<input class='btn btn-primary' type='submit' name='create_job_custom_report' value='Download Custom Billing Report'>
		</div>
	</div>
</form>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
