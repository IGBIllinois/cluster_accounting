<?php
require_once 'includes/header.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

$get_array = array();
$start = 0;
$count = 30;

if (isset($_GET['start']) && is_numeric($_GET['start'])) {
	$start = $_GET['start'];
	$get_array['start'] = $start;
}

$search = "";
if (isset($_GET['search'])) {
        $search = $_GET['search'];
        $get_array['search'] = $search;
}

$enabled = 1;
if (isset($_GET['enabled'])) {
	$enabled = $_GET['enabled'];
}
$all_users = user_functions::get_users($db,$ldap,$search,$enabled);
$num_users = count($all_users);
$get_array = array('search'=>$search,
                'enabled'=>$enabled);

$pages_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($get_array);
$pages_html = html::get_pages_html($pages_url,$num_users,$start,$count);
$users_html = "";
$user_count = 0;

$users_html = html::get_users_rows($all_users,$start,$count);


?>
<h3>List of Users</h3>
<div class='row'>
	<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
        	<div class='form-group'>
                	<input class='form-control' type='text' name='search' placeholder='Search' value='<?php if (isset($search)) { echo $search; } ?>'>
			<input type='hidden' name='enabled' value='<?php echo $enabled; ?>'>
        	        <button type='submit' class='btn btn-primary'>Search</button>
	        </div>
	</form>
        <div class='btn-group pull-right ml-auto' role='group'>
                <a class='btn btn-light' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query(array('search'=>$search)) . "&enabled=1"; ?>'>Active</a>
                <a class='btn btn-light' href='<?php echo $_SERVER['PHP_SELF'] . "?" . http_build_query(array('search'=>$search)) . "&enabled=0"; ?>'>Deactived</a>
        </div>

</div>
<br>
<div class='row'>
<table class='table table-striped table-sm table-bordered'>
	<thead>
		<tr>
			<th>NetID</th>
			<th>Name</th>
			<th>Supervisor</th>
			<th>Administrator</th>
			<th>Active LDAP Account</th>
		</tr>
	</thead>
	<tbody>
	<?php echo $users_html; ?>
	</tbody>
</table>
</div>
<?php echo $pages_html; ?>

<div class='row'>
<form class='form-inline' method='post' action='report.php'>
                <select class='form-control custom-select' name='report_type'>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> <input class='btn btn-primary' type='submit'
                name='create_user_report' value='Download User List'>
</form>

</div>
<?php require_once 'includes/footer.inc.php'; ?>
