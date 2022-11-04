<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
}
else {
	$start_date = date('Ym') . "01";
	$end_date = date('Ymd',strtotime('-1 second',strtotime('+1 month',strtotime($start_date))));
}

$month_name = date('F',strtotime($start_date));
$month = date('m',strtotime($start_date));


$year = date('Y',strtotime($start_date));
$url_navigation = html::get_url_navigation($_SERVER['PHP_SELF'],$start_date,$end_date);

$jobs = job_functions::get_jobs_bill($db,$month,$year);

$jobs_html = "";
$count = 0;
foreach ($jobs as $job) {

	$jobs_html .="<tr><td>" . $job['Username'] . "</td>";
	$jobs_html .= "<td>" . $job['Project'] . "</td>";
	$jobs_html .= "<td>" . $job['Queue'] . "</td>";
	$jobs_html .= "<td>$" . $job['Total Cost'] . "</td>";
	$jobs_html .= "<td>$" . $job['Billed Cost'] . "</td>";
	$jobs_html .= "<td>" . $job['CFOP'] . "</td>";
	$jobs_html .= "<td>" . $job['Activity Code'] ."</td>";

	$jobs_html .= "</tr>";
	$count++;
}

$stats = new statistics($db);

require_once 'includes/header.inc.php';
?>
<h3>Job Billing Monthly Reports - <?php echo $month_name . " " . $year; ?></h3>
<div class='card'>
<ul class='list-group list-group-horizontal'>
        <li class='list-group-item'><a href='<?php echo $url_navigation['back_url']; ?>'>Previous Month</a></li>

        <?php
                $next_month = strtotime('+1 day', strtotime($end_date));
                $today = mktime(0,0,0,date('m'),date('d'),date('y'));
                if ($next_month > $today) {
                        echo "<li class='list-group-item disabled'><a href='#'>Next Month</a></li>";
                }
                else {
                        echo "<li class='list-group-item'><a href='" . $url_navigation['forward_url'] . "'>Next Month</a></li>";
                }
        ?>
</ul>
</div>
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
	<?php echo $jobs_html; ?>
	<tr>
		<td>Monthly Total Cost:</td>
		<td colspan='5'>$<?php echo $stats->get_total_cost($start_date,$end_date,true); ?>
		</td>
	</tr>
	<tr>
		<td>Monthly Billed  Cost:</td>
		<td colspan='5'>$<?php echo $stats->get_total_billed_cost($start_date,$end_date,true); ?>
		</td>
	</tr>
</table>
<br>
<form class='form-inline' action='report.php' method='post'>
	<input type='hidden' name='month' value='<?php echo $month; ?>'>
	<input type='hidden' name='year' value='<?php echo $year; ?>'>
	<select name='report_type' class='form-control custom-select'>
		<option value='xlsx'>Excel 2007</option>
		<option value='csv'>CSV</option>
	</select>&nbsp;
	<input class='btn btn-primary' type='submit' name='create_job_report' value='Download Report'>&nbsp;
	<input class='btn btn-primary' type='submit' name='create_job_boa_report' value='Download BOA Report'>

</form>
<?php

require_once 'includes/footer.inc.php';
?>
