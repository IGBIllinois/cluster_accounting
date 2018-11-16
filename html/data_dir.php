<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

if (isset($_GET['data_dir_id']) && (is_numeric($_GET['data_dir_id']))) {
	$data_dir_id = $_GET['data_dir_id'];
}

if (isset($_POST['remove_data_dir'])) {
	$data_dir_id = $_POST['data_dir_id'];
	$data_dir  = new data_dir($db,$data_dir_id);
	if (!$data_dir->is_default()) {
		$data_dir->disable();
		header('Location: data_dir_custom.php');	
	}

}
$data_dir = new data_dir($db,$data_dir_id);
$project = new project($db,$data_dir->get_project_id());

require_once 'includes/header.inc.php';

?>


<h3>Data Directory - <?php echo $data_dir->get_directory(); ?></h3>

<div class='span6'>
<table class='table table-bordered table-condensed'>
<tr>
	<td>Directory</td>
	<td><?php echo $data_dir->get_directory(); ?></td>
</tr>
<tr>
	<td>Currently Exists</td>
	<td>
	<?php
	if ($data_dir->directory_exists()) {
		echo "<i class='icon-ok'></i>";
	}
	else {
        	echo "<i class='icon-remove'></i>";
        }
	?>
</td>
</tr>
<tr>
	<td>Project</td>
	<td><a href='edit_project.php?project_id=<?php echo $data_dir->get_project_id(); ?>'><?php echo $project->get_name(); ?></a></td>
</tr>

</table>
<?php 

if (!$data_dir->is_default()) {

	echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
	echo "<input type='hidden' name='data_dir_id' value='" . $data_dir_id . "'>";
	echo "<input type='submit' class='btn btn-danger' name='remove_data_dir' value='Remove' onClick='return confirm_delete_dir();'>";
	echo "</form>";

}

?>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
