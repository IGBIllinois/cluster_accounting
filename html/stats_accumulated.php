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

$current_year = new DateTime();

$next_year = DateTime::createFromFormat('Y-m',$year . "-01");
$next_year->modify('first day of next year');

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
$graph_form .= "<select class='form-select' name='graph_type' onChange='document.select_graph.submit();'>";

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

$url_navigation = html::get_url_navigation_year($_SERVER['PHP_SELF'],$year);

require_once 'includes/header.inc.php';

?>
<h3>Accumulated Stats - <?php echo $year; ?></h3>
<div class='row'>
        <div class='col-sm-2'>
        <a class='btn btn-sm btn-primary' href='<?php echo $url_navigation['back_url']; ?>'>Previous Year</a>
	</div>
	<div class='col'>
        <?php
                if ($next_year > $current_year) {
                        echo "<div class='d-flex justify-content-end''><a class='btn btn-sm btn-primary' onclick='return false;'>Next Year</a></div>";
                }
                else {
                        echo "<div class='d-flex justify-content-end''><a class='btn btn-sm btn-primary' href='" . $url_navigation['forward_url'] . "'>Next Year</a></div>";
                }
        ?>
        </div>
</div>
<p>
<div class='row'>
<?php echo $graph_form; ?>
</div>
<br>
<div class='row'>
<?php echo $graph_image; ?>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
