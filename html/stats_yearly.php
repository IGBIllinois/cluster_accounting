<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

$year = date('Y');
if (isset($_GET['year'])) {
	$year = $_GET['year'];
}

$previous_year = $year - 1;
$next_year = $year + 1;
$start_date = $year . "0101";
$end_date = $year . "1231";

$back_url = $_SERVER['PHP_SELF'] . "?year=" . $previous_year;
$forward_url = $_SERVER['PHP_SELF'] . "?year=" . $next_year;

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
//$graph_form .= "?start_date=" . $start_date . "&end_date=" . $end_date . "'>";
$graph_form .= "?year=" . $year . "'>";
$graph_form .= "<select class='custom-select' name='graph_type' onChange='document.select_graph.submit();'>";

foreach ($graph_type_array as $graph) {
        $graph_form .= "<option value='" . $graph['type'] . "' ";
        if ($graph_type == $graph['type']) {
                $graph_form .= "selected='selected'";
        }
        $graph_form .= ">" . $graph['title'] . "</option>\n";


}

$graph_form .= "</select>";

$stats = new statistics($db);

require_once 'includes/header.inc.php';

?>
<h3>Yearly Stats - <?php echo $year; ?></h3>
<nav>
<ul class='pagination'>
        <li class='page-item'><a class='page-link' href='<?php echo $back_url; ?>'>Previous Year</a></li>

        <?php
                $next_year = strtotime('+1 day', strtotime($end_date));
                $today = mktime(0,0,0,date('m'),date('d'),date('y'));
                if ($next_year > $today) {
                        echo "<li class='page-item disabled'><a class='page-link' href='#'>Next Year</a></li>";
                }
                else {
                        echo "<li class='page-item'><a class='page-link' href='" . $forward_url . "'>Next Year</a></li>";
                }
        ?>
</ul>
</nav>
<table class='table table-striped table-bordered table-sm'>
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
	<tr>	<td>Data Billed Cost:</td>
		<td>$<?php echo data_stats::get_billed_cost($db,$start_date,$end_date,true); ?></td>
	</tr>
	<tr>
		<td colspan='2'><?php echo $graph_form; ?></td>
	</tr>
	<tr>
		<td colspan='2'><?php echo $graph_image; ?></td>
	</tr>
</table>
<?php

require_once 'includes/footer.inc.php';
?>
