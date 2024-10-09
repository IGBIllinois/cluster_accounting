<?php
require_once 'includes/main.inc.php';


if (!$login_user->is_admin()) {
        exit;
}
if (isset($_POST['add_project'])) {
	foreach ($_POST as $var) {
		$var = trim(rtrim($var));
	}

	$project = new project($db);

	if (isset($_POST['bill_project'])) {
		$bill_project = 0;
		$default = 0;
		$result = $project->create($_POST['name'],$_POST['ldap_group'],
					$_POST['description'],$default,$bill_project,$_POST['owner']);
	}
	else {
		$bill_project = 1;
		$default = 0;
		$cfop = $_POST['cfop_1'] . "-" . $_POST['cfop_2'] . "-" . $_POST['cfop_3'] . "-" . $_POST['cfop_4'];
		$result = $project->create($_POST['name'],$_POST['ldap_group'],$_POST['description'],
					$default,$bill_project,$_POST['owner'],$cfop,$_POST['activity']);
	}

	if ($result['RESULT']) {
		unset($_POST);
	}
}

elseif (isset($_POST['cancel_project'])) {
	unset($_POST);
}

if (isset($_GET['start']) && is_numeric($_GET['start'])) {
	$start = $_GET['start'];
}
else { $start = 0;
}

$search = "";
if (isset($_GET['search'])) {
        $search = trim(rtrim($_GET['search']));
}

$custom = 'ALL';
if (isset($_GET['custom'])) {
	$custom = $_GET['custom'];
}

$count=30;
$num_projects = functions::get_num_projects($db,$custom,$search);
$get_array = array('search'=>$search,
                'custom'=>$custom);
$pages_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($get_array);
$pages_html = html::get_pages_html($pages_url,$num_projects,$start,$count);
$enabled = 1;
$projects = functions::get_projects($db,$enabled,$custom,$search,$start,$count);
$projects_html = "";

foreach ($projects as $project) {

	$projects_html .= "<tr>";
	$projects_html .= "<td><a href='edit_project.php?project_id=" . $project['project_id'] . "'>" . $project['project_name'] . "</a></td>";
	$projects_html .= "<td>" . $project['owner'] . "</td>";
	$projects_html .= "<td>" . $project['project_ldap_group'] . "</td>";
	$projects_html .= "<td>" . $project['project_description'] . "</td>";
	$projects_html .= "<td>" . $project['cfop_billtype'] . "</td>";
	$projects_html .= "<td>" . $project['cfop_value'] . "</td>";
	$projects_html .= "<td>" . $project['cfop_activity'] . "</td>";
	$projects_html .= "<td>" . $project['cfop_time_created'] . "</td>";
	$projects_html .= "</tr>";
}

$users = user_functions::get_users($db);

require_once 'includes/header.inc.php';

?>
<h3>Projects</h3>
<hr>
<div class='row'>
	<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
		<div class='form-group'>
                <input class='form-control' type='text' name='search' placeholder='Search'
                        value='<?php if (isset($search)) { echo $search; } ?>' autocapitalize='none'>
		<input type='hidden' name='custom' value='<?php echo $custom; ?>'>
                <button type='submit' class='btn btn-primary'>Search</button>
		</div>
	</form>
	<div class='btn-group pull-right ml-auto' role='group' aria-label='test'>
		<a class='btn btn-primary' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query(array('search'=>$search)) . "&custom=ALL"; ?>'>All Projects</a>
		<a class='btn btn-secondary' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query(array('search'=>$search)) . "&custom=CUSTOM"; ?>'>Custom Projects</a>
		<a class='btn btn-info' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query(array('search'=>$search)) . "&custom=DEFAULT"; ?>'>User Projects</a>
	</div>

</div>
<br>
<div class='row'>
<table class='table table-bordered table-sm table-striped'>
	<thead>
		<tr>
			<th>Name</th>
			<th>Owner</th>
			<th>LDAP Group</th>
			<th>Description</th>
			<th>Bill Type</th>
			<th>CFOP</th>
			<th>Activity Code</th>
			<th>Time Set</th>
		</tr>
	</thead>
	<?php echo $projects_html; ?>
</table>
</div>
<div class='row justify-content-center'>
<?php echo $pages_html; ?>
</div>
<form method='post' action='report.php'>
        <input type='hidden' name='search' value='<?php echo $search; ?>'>
	<input type='hidden' name='custom' value='<?php echo $custom; ?>'>
        <div class='row'>
		<div class='col-sm-1'>
			<select class='form-select' name='report_type'>
				<option value='xlsx'>Excel</option>
				<option value='csv'>CSV</option>
			</select>
		</div>
        	<div class='col'>
			<input class='btn btn-primary' type='submit' name='project_report' value='Download Projects Report'>
		</div>
	</div>

</form>

<div class='row'>
<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}
?>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
