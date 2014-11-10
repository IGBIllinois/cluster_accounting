<?php
require_once 'includes/header.inc.php';

$graph_type_array[0]['type'] = 'user_num_jobs_per_month';
$graph_type_array[0]['title'] = 'Jobs Per Month';

$graph_type_array[1]['type'] = 'user_billed_cost_per_month';
$graph_type_array[1]['title'] = 'Billed Job Cost Per Month';

$graph_type_array[2]['type'] = 'user_total_cost_per_month';
$graph_type_array[2]['title'] = 'Total Job Cost Per Month';

//Default Graph Settings
$user_id = $login_user->get_user_id();
$year = date('Y');
$graph_type = $graph_type_array[0]['type'];
if (isset($_POST['get_job_graph'])) {
	$year = $_POST['year'];
	$user_id = $_POST['user_id'];
	$graph_type = $_POST['graph_type'];

}

if (!$login_user->permission($user_id)) {
        echo "<div class='alert alert-error'>Iinvalid Permissions</div>";
        exit;
}


$start_date = $year . "0101";
$end_date = $year . "1231";


$get_array = array('year'=>$year,'graph_type'=>$graph_type,'user_id'=>$user_id);
$graph_image = "<img src='graph.php?" . http_build_query($get_array) . "'>";

$graph_form = "<select name='graph_type'>";

foreach ($graph_type_array as $graph) {
        $graph_form .= "<option value='" . $graph['type'] . "' ";
        if ($graph_type == $graph['type']) {
                $graph_form .= "selected='selected'";
        }
        $graph_form .= ">" . $graph['title'] . "</option>\n";


}

$graph_form .= "</select>";

$year_form = "<select name='year' class='input-small'>";
for ($i=2010;$i<=date('Y');$i++) {
	if ($i == $year) {
		$year_form .= "<option value='" . $i . "' selected='selected'>" . $i . "</option>";
	}
	else {
		$year_form .= "<option value='" . $i . "'>" . $i . "</option>";
	}

}
$year_form .= "</select>";
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
        $user_list_html = "<select class='input-small' name='user_id'>";
        if ((!isset($_GET['user_id'])) || ($_GET['user_id'] == $login_user->get_user_id())) {
                $user_list_html .= "<option value='" . $login_user->get_user_id(). "' selected='selected'>";
                $user_list_html .= $login_user->get_username() . "</option>";
        }
        else {
                $user_list_html .= "<option value='" . $login_user->get_user_id() . "'>";
                $user_list_html .= $login_user->get_username() . "</option>";
        }

        foreach ($user_list as $user) {

                if ($user['user_id'] == $user_id) {
                        $user_list_html .= "<option value='" . $user['user_id'] . "' selected='true'>" . $user['user_name'] . "</option>";
                }
                else {
                        $user_list_html .= "<option value='" . $user['user_id'] . "'>" . $user['user_name'] . "</option>";
                }

        }
        $user_list_html .= "</select>";
}




?>
<h3>Yearly Stats - <?php echo $year; ?></h3>
<form class='form-inline' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php 
	if ($login_user->is_supervisor() || $login_user->is_admin()) {
	echo "<label class='inline'>Username:</label>";
	echo $user_list_html;

	}
	else {
		echo "<input type='hidden' name='user_id' value='" . $user_id . "'>";
	}
?>
	<label class='inline'>Year:</label>
	<?php echo $year_form; ?>
	<label class='inline'>Graph:</label>
	<?php echo $graph_form; ?>
	<input class='btn btn-primary' type='submit' name='get_job_graph' value='Get Graph'>
</form>
<div class=row'>
<?php echo $graph_image; ?>
</div>

<?php

include_once 'includes/footer.inc.php';
?>
