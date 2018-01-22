<?php
require_once 'includes/header.inc.php';

if (!$login_user->is_admin()) {
        exit;
}
$year = date('Y');
if (isset($_GET['year'])) {
	$year = $_GET['year'];
}
$previous_year = $year - 1;
$next_year =$year + 1;
$forward_url = $_SERVER['PHP_SELF'] . "?year=" . $next_year;
$back_url = $_SERVER['PHP_SELF'] . "?year=" . $previous_year;

$graph_type_array[0]['type'] = 'jobs_per_month';
$graph_type_array[0]['title'] = 'Jobs Per Month';

$graph_type_array[1]['type'] = 'billed_cost_per_month';
$graph_type_array[1]['title'] = 'Billed Job Cost Per Month';

$graph_type_array[2]['type'] = 'total_cost_per_month';
$graph_type_array[2]['title'] = 'Total Job Cost Per Month';

$graph_type_array[3]['type'] = 'data_usage_per_month';
$graph_type_array[3]['title'] = 'Data Usage Per Month';



$graph_type = $graph_type_array[0]['type'];
if (isset($_POST['graph_type'])) {
        $graph_type = $_POST['graph_type'];
}

$get_array = array('year'=>$year,'graph_type'=>$graph_type);
$graph_image = "<img src='graph.php?" . http_build_query($get_array) . "'>";

$graph_form = "<form name='select_graph' id='select_graph' method='post' action='" . $_SERVER['PHP_SELF'] . "?year=" . $year . "'>";
$graph_form .= "<select class='input-xlarge' name='graph_type' onChange='document.select_graph.submit();'>";

foreach ($graph_type_array as $graph) {
	$graph_form .= "<option value='" . $graph['type'] . "' ";
	if ($graph_type == $graph['type']) {
        	$graph_form .= "selected='selected'";
	}
	$graph_form .= ">" . $graph['title'] . "</option>\n";


}

$graph_form .= "</select></form>";

?>
<h3>Accumulated Stats - <?php echo $year; ?></h3>
<ul class='pager'>
        <li class='previous'><a href='<?php echo $back_url; ?>'>Previous Year</a></li>

        <?php
		$this_year = date("Y");
                if ($next_year > $this_year) {
                        echo "<li class='next disabled'><a href='#'>Next Year</a></li>";
                }
                else {
                        echo "<li class='next'><a href='" . $forward_url . "'>Next Year</a></li>";
                }
        ?>
</ul>
<?php echo $graph_form; ?>
<div class='row span12'>
<?php echo $graph_image; ?>
</div>
<?php

include_once 'includes/footer.inc.php';
?>
