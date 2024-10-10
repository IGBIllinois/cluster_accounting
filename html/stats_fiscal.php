<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

$selected_year = new DateTime(date('Y-01-01 00:00:00'));
if (isset($_GET['year'])) {
        $year = $_GET['year'];
	$selected_year = DateTime::createFromFormat("Y-m-d H:i:s",$year . "-01-01 00:00:00");
}

$year = $selected_year->format('Y');

//////Year////////
$min_year = job_bill::get_minimal_year($db);
$year_html = "<select class='form-select' name='year'>";
for ($i=$min_year; $i<=date("Y");$i++) {
        if ($i == $year) { $year_html .= "<option value='" . $i . "' selected='true'>" . $i . "</option>"; }
        else { $year_html .= "<option value='" . $i . "'>" . $i . "</option>"; }
}
$year_html .= "</select>";

$start_date = DateTime::createFromFormat("Y-m-d H:i:s",$year . "-07-01 00:00:00");
$end_date = DateTime::createFromFormat("Y-m-d H:i:s",$year+1 . "-06-30 23:59:59");

$url_navigation = html::get_url_navigation_year($_SERVER['PHP_SELF'],$year);

$next_year = DateTime::createFromFormat('Y-m',$year . "-01");
$next_year->modify('first day of next year');
$current_year = new DateTime();

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
		'start_date'=>$start_date->format("Ymd"),
		'end_date'=>$end_date->format("Ymd")
	);
$graph_image = "<img src='graph.php?" . http_build_query($get_array) . "'>";


$graph_form = "<form class='form-inline' name='select_graph' id='select_graph' method='post' action='" . $_SERVER['PHP_SELF'];
$graph_form .= "?year=" . $selected_year->format("Y") . "'>";
$graph_form .= "<select class='form-select' name='graph_type' onChange='document.select_graph.submit();'>";

foreach ($graph_type_array as $graph) {
        $graph_form .= "<option value='" . $graph['type'] . "' ";
        if ($graph_type == $graph['type']) {
                $graph_form .= "selected='selected'";
        }
        $graph_form .= ">" . $graph['title'] . "</option>\n";


}

$graph_form .= "</select></form>";

$stats = new statistics($db);

require_once 'includes/header.inc.php';

?>
<h3>Fiscal Yearly Stats - <?php echo $year; ?></h3>
<form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get'>
	<div class='row'>
		<div class='col-auto'>
			<label class='form-label' for='year'>Year:</label>
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
        <a class='btn btn-sm btn-primary' href='<?php echo $url_navigation['back_url']; ?>'>Previous Year</a>
	</div>
	<div class='col'>

        <?php
                if ($next_year > $current_year) {
                        echo "<div class='d-flex justify-content-end'><a class='btn btn-sm btn-primary' onclick='return false;'>Next Year</a></div>";
                }
                else {
                        echo "<div class='d-flex justify-content-end'><a class='btn btn-sm btn-primary' href='" . $url_navigation['forward_url'] . "'>Next Year</a></div>";
                }
        ?>
        </div>
</div>
<p>
<table class='table table-striped table-bordered table-sm'>
	<tr>
		<td>Number Of Jobs:</td>
		<td><?php echo $stats->get_num_jobs($start_date,$end_date,true); ?></td>
	</tr>
	<tr>
		<td>Job Total Cost:</td>
		<td>$<?php echo $stats->get_job_total_cost($start_date,$end_date,true); ?>
		</td>
	</tr>
	<tr>
		<td>Job Billed Cost:</td>
		<td>$<?php echo $stats->get_job_total_billed_cost($start_date,$end_date,true); ?>
		</td>
	</tr>
	<tr>
		<td>Data Total Cost:</td>
		<td>$<?php echo data_stats::get_total_cost($db,$start_date,$end_date,true); ?></td>
	</tr>
	<tr>	<td>Data Billed Cost:</td>
		<td>$<?php echo data_stats::get_billed_cost($db,$start_date,$end_date,true); ?></td>
	</tr>
	<tr>
		<td colspan='2'><div class='col-sm-2'><?php echo $graph_form; ?></div></td>
	</tr>
	<tr>
		<td colspan='2'><?php echo $graph_image; ?></td>
	</tr>
</table>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
