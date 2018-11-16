<?php
require_once 'includes/header.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

if (isset($_GET['start']) && is_numeric($_GET['start'])) {
	$start = $_GET['start'];
}
else { $start = 0;
}
$custom = 0;
$num_dirs = data_functions::get_num_directories($db,$custom);
$count = 30;
$pages_url = $_SERVER['PHP_SELF'];
$pages_html = html::get_pages_html($pages_url,$num_dirs,$start,$count);

$directories = data_functions::get_directories($db,$custom,$start,$count);
$dir_html = $dir_html = html::get_data_dir_rows($directories);


	
?>
<h3>List of Directories</h3>
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
<?php 
	echo $pages_html;


require_once 'includes/footer.inc.php';
?>
