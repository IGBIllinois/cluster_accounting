<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
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

$stats = new statistics($db);

$url_navigation = html::get_url_navigation($_SERVER['PHP_SELF'],$start_date,$end_date);

$graph_type_array[0]['type'] = 'top_job_users';
$graph_type_array[0]['title'] = 'Top Users';

$graph_type_array[1]['type'] = 'users_top_total_cost';
$graph_type_array[1]['title'] = 'Top Total Cost Users';

$graph_type_array[2]['type'] = 'users_top_billed_cost';
$graph_type_array[2]['title'] = 'Top Billed Cost Users';

$graph_type_array[3]['type'] = 'top_data_usage';
$graph_type_array[3]['title'] = 'Top Data Usage';

$graph_type = $graph_type_array[0]['type'];
if (isset($_POST['graph_type'])) {
	$graph_type = $_POST['graph_type'];

}
$get_array  = array('graph_type'=>$graph_type,
		'start_date'=>$start_date,
		'end_date'=>$end_date);
$graph_image = "<img src='graph.php?" . http_build_query($get_array) . "'>";


$graph_form = "<form class='form-inline' name='select_graph' id='select_graph' method='post' action='" . $_SERVER['PHP_SELF'];
$graph_form .= "?start_date=" . $start_date . "&end_date=" . $end_date . "'>";
$graph_form .= "<select class='custom-select' name='graph_type' onChange='document.select_graph.submit();'>";

foreach ($graph_type_array as $graph) {
        $graph_form .= "<option value='" . $graph['type'] . "' ";
        if ($graph_type == $graph['type']) {
                $graph_form .= "selected='selected'";
        }
        $graph_form .= ">" . $graph['title'] . "</option>\n";


}

$graph_form .= "</select>";

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
