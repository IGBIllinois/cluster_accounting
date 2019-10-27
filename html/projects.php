<?php
require_once 'includes/header.inc.php';


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
$projects = functions::get_projects($db,$custom,$search,$start,$count);
$projects_html = "";

foreach ($projects as $project) {

	if ($project['cfop_bill']) {
		$project_bill = "<i class='icon-ok'></i>";
	}
	else {
		$project_bill = "<i class='icon-remove'></i>";
	}
	$projects_html .= "<tr>";
	$projects_html .= "<td><a href='edit_project.php?project_id=" . $project['project_id'] . "'>" . $project['project_name'] . "</a></td>";
	$projects_html .= "<td>" . $project['owner'] . "</td>";
	$projects_html .= "<td>" . $project['project_ldap_group'] . "</td>";
	$projects_html .= "<td>" . $project['project_description'] . "</td>";
	$projects_html .= "<td>" . $project_bill . "</td>";
	$projects_html .= "<td>" . $project['cfop_value'] . "</td>";
	$projects_html .= "<td>" . $project['cfop_activity'] . "</td>";
	$projects_html .= "<td>" . $project['cfop_time_created'] . "</td>";
	$projects_html .= "</tr>";
}

$users = user_functions::get_users($db);
$owner_html = "<select name='owner' id='owner_input' class='input'>";
foreach ($users as $owner) {
	if ((isset($_POST['owner'])) && ($_POST['owner'] == $owner['user_id'])) {
		$owner_html .= "<option value='" . $owner['user_id'] . "' selected='selected'>" . $owner['user_name'] . "</option>";
	}
	else {
		$owner_html .= "<option value='" . $owner['user_id'] . "'>" . $owner['user_name'] . "</option>";
	}

}
$owner_html .= "</select>";
?>
<h3>Projects</h3>
<div class='row'>
<form class='span6 form-search' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
		<div class='input-append'>
                <input type='text' name='search' class='input-xlarge search-query' placeholder='Search'
                        value='<?php if (isset($search)) { echo $search; } ?>' autocapitalize='none'>
		<input type='hidden' name='custom' value='<?php echo $custom; ?>'>
                <button type='submit' class='btn btn-primary'>Search</button>
		</div>
</form>
<div class='span6 btn-toolbar text-right'>
	<div class='btn-group'>
		<a class='btn' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query(array('search'=>$search)) . "&custom=ALL"; ?>'>All Projects</a>
		<a class='btn' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query(array('search'=>$search)) . "&custom=CUSTOM"; ?>'>Custom Projects</a>
		<a class='btn' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query(array('search'=>$search)) . "&custom=DEFAULT"; ?>'>User Projects</a>
	</div>

</div>
</div>
<div class='row'>
<table class='table table-bordered table-sm table-striped'>
	<thead>
		<tr>
			<th>Name</th>
			<th>Owner</th>
			<th>LDAP Group</th>
			<th>Description</th>
			<th>Bill</th>
			<th>CFOP</th>
			<th>Activity Code</th>
			<th>Time Set</th>
		</tr>
	</thead>
	<?php echo $projects_html; ?>
</table>
<?php echo $pages_html; ?>
<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}
?>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
