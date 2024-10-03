<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

$selected_month = new DateTime(date('Y-m-01 00:00:00'));

if (isset($_GET['year']) && isset($_GET['month'])) {
        $year = $_GET['year'];
        $month = $_GET['month'];
        $selected_month = DateTime::createFromFormat("Y-m-d H:i:s",$year . "-" . $month . "-01 00:00:00");
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

$stats = new statistics($db);

$url_navigation = html::get_url_navigation_month($_SERVER['PHP_SELF'],$year,$month);

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
		'start_date'=>$selected_month->format("Ymd"),
		'end_date'=>$selected_month->format("Ymd")
	);
$graph_image = "<img src='graph.php?" . http_build_query($get_array) . "'>";


$graph_form = "<form class='form-inline' name='select_graph' id='select_graph' method='post' action='" . $_SERVER['PHP_SELF'];
$graph_form .= "?year=" . $selected_month->format("Y") . "&month=" . $selected_month->format("m") . "'>";
$graph_form .= "<select class='form-select' name='graph_type' onChange='document.select_graph.submit();'>";

foreach ($graph_type_array as $graph) {
        $graph_form .= "<option value='" . $graph['type'] . "' ";
        if ($graph_type == $graph['type']) {
                $graph_form .= "selected='selected'";
        }
        $graph_form .= ">" . $graph['title'] . "</option>\n";


}

$graph_form .= "</select></form>";

require_once 'includes/header.inc.php';

?>

<h3>Monthly Stats - <?php echo $month_name . " " . $year; ?></h3>
<form class='form-inline' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get'>
<div class='form-group'>
        <label for='month'>Month:</label>
        &nbsp;<?php echo $month_html; ?>
</div>&nbsp;
<div class='form-group'>
        <label for='year'>Year:</label>
        &nbsp; <?php echo $year_html; ?>
</div>
&nbsp;
<div class='form-group'>
        <button class='btn btn-primary' type='submit' name='selectedDate'>Get Records</button>
</div>
</form>
<p>
<div class='row'>
        <div class='col-sm-12 col-md-12 col-lg-12 col-xl-12'>
        <a class='btn btn-sm btn-primary' href='<?php echo $url_navigation['back_url']; ?>'>Previous Month</a>

        <?php
                if ($next_month > $current_month) {
                        echo "<div class='float-right'><a class='btn btn-sm btn-primary' onclick='return false;'>Next Month</a></div>";
                }
                else {
                        echo "<div class='float-right'><a class='btn btn-sm btn-primary' href='" . $url_navigation['forward_url'] . "'>Next Month</a></div>";
                }
        ?>
        </div>
</div>
<p>
<table class='table table-striped table-bordered table-sm'>
	<tbody>
		<tr>
			<td>Number Of Jobs:</td>
			<td><?php echo $stats->get_num_jobs($selected_month,$selected_month,true); ?></td>
		</tr>
		<tr>
			<td>Job Total Cost:</td>
			<td>$<?php echo $stats->get_job_total_cost($selected_month,$selected_month,true); ?>
			</td>
		</tr>
		<tr>
			<td>Job Billed Cost:</td>
			<td>$<?php echo $stats->get_job_total_billed_cost($selected_month,$selected_month,true); ?>
			</td>
		</tr>
	        <tr>
	                <td>Data Total Cost:</td>
        	        <td>$<?php echo data_stats::get_total_cost($db,$selected_month,$selected_month,true); ?></td>
	        </tr>
        	<tr>    
			<td>Data Billed Cost:</td>
                	<td>$<?php echo data_stats::get_billed_cost($db,$selected_month,$selected_month,true); ?></td>
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
