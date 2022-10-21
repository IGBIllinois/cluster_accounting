<?php
require_once 'includes/header.inc.php';


if (!$login_user->is_admin()) {
        exit;
}

if (isset($_POST['add_dir'])) {
	foreach($_POST as $var) {
		$var = trim(rtrim($var));
	}
	$data_dir = new data_dir($db);
	$default = 0;
	$result = $data_dir->create($_POST['project_id'],$_POST['directory'],$default);
	if ($result['RESULT']) {
		unset($_POST);
		header('Location: data_dir_custom.php');
	}
}

elseif (isset($_POST['cancel_dir'])) {
	unset($_POST);
}


$projects = functions::get_projects($db);
$projects_html = "";
foreach ($projects as $project) {
	$projects_html .= "<option value='" . $project['project_id'] . "'>";
	$projects_html .= $project['project_name'] . "</option>";
	
	
}
?>
<div class='col-sm-4 col-md-4 col-lg-4 col-xl-4'>
<form class='form' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>' name='form'>
	<fieldset>
		<legend>Add Data Directory</legend>
		<div class='form-group'>
			<label for='directory_input'>Directory:</label>
			<div class='controls'>
				<input class='form-control' type='text' name='directory' id='directory_input'
					value='<?php if (isset($_POST['directory'])) { echo $_POST['directory']; } ?>'>
			</div>
		</div>
		<div class='form-group'>
			<label for='project_input'>Project:</label>
			<select class='form-control custom-select' name='project_id' id='project_input'>
				<?php echo $projects_html; ?>
			</select>
		</div>
		<div class='form-group'>
				<input class='btn btn-primary' type='submit' name='add_dir' value='Add Directory'> 
				<input class='btn btn-warning' type='submit' name='cancel_dir' value='Cancel'>
		</div>
	</fieldset>
</form>
</div>

<?php
if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; }
?>
</div>

<?php 
require_once 'includes/footer.inc.php';
?>
