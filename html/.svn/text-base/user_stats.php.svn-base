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
$start_date = date('Ym') . "01";
$end_date = date('Ymd',strtotime('-1 second',strtotime('+1 month',strtotime($start_date))));
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
}

$month_name = date('F',strtotime($start_date));
$year = date('Y',strtotime($start_date));


$url_navigation = html::get_url_navigation($_SERVER['PHP_SELF'],$start_date,$end_date);


$user_stats = new user_stats($db,$user_id,$start_date,$end_date);
$format = 1;
?>
<h3>Monthly Stats - <?php echo $month_name . " " . $year; ?></h3>
<ul class='pager'>
        <li class='previous'><a href='<?php echo $url_navigation['back_url']; ?>'>Previous Month</a></li>

        <?php
                $next_month = strtotime('+1 day', strtotime($end_date));
                $today = mktime(0,0,0,date('m'),date('d'),date('y'));
                if ($next_month > $today) {
                        echo "<li class='next disabled'><a href='#'>Next Month</a></li>";
                }
                else {
                        echo "<li class='next'><a href='" . $url_navigation['forward_url'] . "'>Next Month</a></li>";
                }
        ?>
</ul>

<table class='table table-condensed table-striped table-bordered'>
	<tr>
		<td>Number of Jobs</td>
		<td><?php echo $user_stats->get_num_jobs($format); ?></td>
	</tr>
	<tr>
		<td>Total Job Cost</td>
		<td>$<?php echo $user_stats->get_total_cost($format); ?></td>
	</tr>
	<tr>
		<td>Billed Job Cost</td>
		<td>$<?php echo $user_stats->get_billed_cost($format); ?></td>
	</tr>
	<tr>
		<td>Average Job Length</td>
		<td><?php echo $user_stats->get_avg_elapsed_time(); ?></td>
	</tr>
	<tr>
		<td>Max Job Length</td>
		<td><?php echo $user_stats->get_max_job_length(); ?></td>
	</tr>	
	<tr>
		<td>Number of Completed Jobs (exit status=0)</td>
		<td><?php echo $user_stats->get_num_completed_jobs(); ?></td>
	</tr>
	<tr>
		<td>Number of Failed/Canceled Jobs (exit status !=0)</td>
		<td><?php echo $user_stats->get_num_failed_jobs(); ?></td>
	</tr>
</table>

<?php

include_once 'includes/footer.inc.php';
?>
