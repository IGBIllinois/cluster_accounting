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


$all_users = user_functions::get_users($db,$ldap,$search);
$num_users = count($all_users);
$pages_url = $_SERVER['PHP_SELF'] . "?" . http_build_query($get_array);
$pages_html = html::get_pages_html($pages_url,$num_users,$start,$count);
$users_html = "";
$user_count = 0;

$users_html = html::get_users_rows($all_users,$start,$count);


?>
<h3>List of Users</h3>
<form class='form-search' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
        <div class='input-append'>
                <input type='text' name='search' class='input-long search-query' value='<?php if (isset($search)) { echo $search; } ?>'>
                <input type='submit' class='btn' value='Search'>
        </div>
</form>

<table class='table table-striped table-condensed table-bordered'>
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
<form class='form-inline' method='post' action='report.php'>
                <select name='report_type' class='input-medium'>
                <option value='xls'>Excel 2003</option>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> <input class='btn btn-primary' type='submit'
                name='create_user_report' value='Download User List'>
</form>

<?php echo $pages_html; ?>
<?php include_once 'includes/footer.inc.php'; ?>
