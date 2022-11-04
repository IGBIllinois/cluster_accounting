<?php
require_once 'includes/main.inc.php';

$user_id = $login_user->get_user_id();
if (isset($_GET['user_id']) && (is_numeric($_GET['user_id']))) {
        $user_id = $_GET['user_id'];
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
$year = date('Y',strtotime($start_date));


$url_navigation = html::get_url_navigation($_SERVER['PHP_SELF'],$start_date,$end_date);

$stats = new statistics($db);

if (isset($_POST['graph_type'])) {

	$graph_type = $_POST['graph_type'];
	$graph_image = "<img src='graphs/graph_" . $graph_type . ".php?start_date=" . $start_date . "&end_date=" . $end_date . "'>";

}
else {
	$graph_image = "<img src='graphs/graph_users_jobs.php?start_date=" . $start_date . "&end_date=" . $end_date . "'>";
	$graph_type = "users_jobs";
}

$graph_form = "<form class='form-inline' name='select_graph' id='select_graph' method='post' action='stats_monthly.php?start_date=" . $start_date . "&end_date=" . $end_date . "'>";
$graph_form .= "<select class='custom-select' name='graph_type' onChange='document.select_graph.submit();'>";

if ($graph_type == "users_jobs") {
        $graph_form .= "<option value='user_jobs' selected='selected'>User's Jobs</option>";
}
else { $graph_form .= "<option value='users_jobs'>Users's Jobs</option>";
}
if ($graph_type == "users_total_cost") {
        $graph_form .= "<option value='users_total_cost' selected='selected'>User's Total Cost</option>";
}
else { $graph_form .= "<option value='users_total_cost'>User's Total Cost</option>";
}
if ($graph_type == "users_billed_cost") {
        $graph_form .= "<option value='users_billed_cost' selected='selected'>Users Billed Cost</option>";
}
else { $graph_form .= "<option value='users_billed_cost'>Users Billed Cost</option>";
}

require_once 'includes/header.inc.php';

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

<table class='table table-striped table-bordered table-sm'>
	<tbody>
		<tr>
			<td>Number Of Jobs:</td>
			<td><?php echo $stats->get_num_jobs($start_date,$end_date,true); ?></td>
		</tr>
		<tr>
			<td>Job Total Cost:</td>
			<td>$<?php echo $stats->get_total_cost($start_date,$end_date,true); ?>
			</td>
		</tr>
		<tr>
			<td>Job Billed Cost:</td>
			<td>$<?php echo $stats->get_total_billed_cost($start_date,$end_date,true); ?>
			</td>
		</tr>
		<tr>
			<td>Longest Job (HH:MM:SS):</td>
			<td><?php echo $stats->get_longest_job($start_date,$end_date); ?></td>
		</tr>
		<tr>
			<td>Average Job Length (HH:MM:SS):</td>
			<td><?php echo $stats->get_avg_job($start_date,$end_date); ?></td>
		</tr>
		<tr>
			<td>Average Job Wait:</td>
			<td><?php echo $stats->get_avg_wait($start_date,$end_date); ?></td>
		</tr>
	        <tr>
	                <td>Data Total Cost:</td>
        	        <td>$<?php echo data_stats::get_total_cost($db,$start_date,$end_date,true); ?></td>
	        </tr>
        	<tr>    
			<td>Data Billed Cost:</td>
                	<td>$<?php echo data_stats::get_billed_cost($db,$start_date,$end_date,true); ?></td>
	        </tr>
		<tr>
			<td colspan='2'><?php echo $graph_form; ?></td>
		</tr>
		<tr>
			<td colspan='2'><?php echo $graph_image; ?></td>
		</tr>
	</tbody>
</table>

<?php

require_once 'includes/footer.inc.php';
?>
