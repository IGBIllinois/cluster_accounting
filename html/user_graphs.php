<?php
require_once 'includes/main.inc.php';

$user_id = $login_user->get_user_id();
if (!$login_user->permission($user_id)) {
        echo "<div class='alert alert-error'>Invalid Permissions</div>";
        exit;
}
$graph_type_array[0]['type'] = 'user_num_jobs_per_month';
$graph_type_array[0]['title'] = 'Jobs Per Month';

$graph_type_array[1]['type'] = 'user_billed_cost_per_month';
$graph_type_array[1]['title'] = 'Billed Job Cost Per Month';

$graph_type_array[2]['type'] = 'user_total_cost_per_month';
$graph_type_array[2]['title'] = 'Total Job Cost Per Month';

$selected_year = new DateTime(date('Y-01-01 00:00:00'));

$year = $selected_year->format('Y');

$graph_type = $graph_type_array[0]['type'];
if (isset($_POST['get_job_graph'])) {
	$year = $_POST['year'];
	$selected_year = DateTime::createFromFormat("Y-m-d H:i:s",$year . "-01-01 00:00:00");
        $user_id = $_POST['user_id'];
        $graph_type = $_POST['graph_type'];

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

$start_date = DateTime::createFromFormat("Y-m-d H:i:s",$year . "-01-01 00:00:00");
$end_date = DateTime::createFromFormat("Y-m-d H:i:s",$year . "-12-31 23:59:59");

$url_navigation = html::get_url_navigation_year($_SERVER['PHP_SELF'],$year);

$next_year = DateTime::createFromFormat('Y-m',$year . "-01");
$next_year->modify('first day of next year');
$current_year = new DateTime();


$get_array = array('year'=>$year,'graph_type'=>$graph_type,'user_id'=>$user_id);
$graph_image = "<img src='graph.php?" . http_build_query($get_array) . "'>";

$graph_form = "<select class='form-select' name='graph_type'>";

foreach ($graph_type_array as $graph) {
        $graph_form .= "<option value='" . $graph['type'] . "' ";
        if ($graph_type == $graph['type']) {
                $graph_form .= "selected='selected'";
        }
        $graph_form .= ">" . $graph['title'] . "</option>\n";


}

$graph_form .= "</select>";


$graph_image = "<img src='graph.php?" . http_build_query($get_array) . "'>";


$graph_form = "<form class='d-flex flex-row align-items-center flex-wrap' name='select_graph' id='select_graph' method='post' action='" . $_SERVER['PHP_SELF'];
$graph_form .= "?year=" . $selected_year->format("Y") . "'>";
$graph_form .= "<select class='form-select' name='graph_type' onChange='document.select_graph.submit();'>";

foreach ($graph_type_array as $graph) {
        $graph_form .= "<option value='" . $graph['type'] . "' ";
        if ($graph_type == $graph['type']) {
                $graph_form .= "selected='selected'";
        }
        $graph_form .= ">" . $graph['title'] . "</option>\n";


}

$graph_form .= "</select>";

//list of users to select from
$user_list = array();
if ($login_user->is_supervisor()) {
        $user_list = $login_user->get_supervising_users();
}
if ($login_user->is_admin()) {
        $user_list = user_functions::get_users($db);
}
$user_list_html = "";
if (count($user_list)) {
        $user_list_html = "<select class='form-select' name='user_id' id='user_id_input'>";
        $user_list_html .= "<option></option>";
        if ((!isset($_GET['user_id'])) || ($_GET['user_id'] == $login_user->get_user_id())) {
                $user_list_html .= "<option value='" . $login_user->get_user_id(). "' selected='selected'>";
                $user_list_html .= $login_user->get_username() . "</option>\n";
        }
        else {
                $user_list_html .= "<option value='" . $login_user->get_user_id() . "'>";
                $user_list_html .= $login_user->get_username() . "</option>\n";
        }

        foreach ($user_list as $user) {

                if ($user['user_id'] == $user_id) {
                        $user_list_html .= "<option value='" . $user['user_id'] . "' selected='selected'>" . $user['user_name'] . "</option>\n";
                }
                else {
                        $user_list_html .= "<option value='" . $user['user_id'] . "'>" . $user['user_name'] . "</option>\n";
                }

        }
        $user_list_html .= "</select>";
}


require_once 'includes/header.inc.php';

?>
<h3>User Stats - <?php echo $year; ?></h3>
<form class='d-flex flex-row align-items-center flex-wrap' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php
        if ($login_user->is_supervisor() || $login_user->is_admin()) {
        echo "<label class='my-1 me-2'>Username:</label>&nbsp;";
        echo $user_list_html;

        }
        else {
                echo "<input type='hidden' name='user_id' value='" . $user_id . "'>";
        }
?>
	&nbsp;
        <label class='my-1 me-2'>Year:</label>&nbsp;
        <?php echo $year_html; ?>&nbsp;
        <label class='my-1 me-2'>Graph:</label>&nbsp;
        <?php echo $graph_form; ?>
        &nbsp;<input class='btn btn-primary' type='submit' name='get_job_graph' value='Get Graph'>
</form>

<p>
<div class='row'>
<?php echo $graph_image; ?>
</div>

<script type="text/javascript">
$(document).ready(function() {
        $('#user_id_input').select2({
                'placeholder': "Select a User"
        });


});
</script>

<?php

require_once 'includes/footer.inc.php';
?>

