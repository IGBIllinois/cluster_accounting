<?php
require_once 'includes/header.inc.php';

if (isset($_GET['start']) && is_numeric($_GET['start'])) {
	$start = $_GET['start'];
}
else { $start = 0;
}

$home_dir = 1;
$num_dirs = data_functions::get_num_directories($db,$home_dir);
$count = 30;
$pages_url = $_SERVER['PHP_SELF'];
$pages_html = html::get_pages_html($pages_url,$num_dirs,$start,$count);

$directories = data_functions::get_directories($db,$home_dir,$start,$count);
$dir_html = html::get_data_dir_rows($directories);


$projects = functions::get_projects($db);
$projects_html = "";
foreach ($projects as $project) {
	$projects_html .= "<option value='" . $project['project_id'] . "'>";
	$projects_html .= $project['project_name'] . "</option>";
	
	
}
?>
<h3>List of Home Directories</h3>
<table class='table table-striped table-condensed table-bordered'>
	<thead>
		<tr>
			<th>Directory</th>
			<th>Currently Exists</th>
			<th>Project</th>
			<th>Time Created</th>
		</tr>
	</thead>
	<tbody>
		<?php echo $dir_html; ?>
	</tbody>
</table>
<?php echo $pages_html; ?>


<?php
if (isset($message)) { echo $message; }
require_once 'includes/footer.inc.php';
?>
