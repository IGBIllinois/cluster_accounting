<?php
require_once 'includes/main.inc.php';


if (!$login_user->is_admin()) {
        exit;
}

if (isset($_POST['add_dir'])) {
	$_POST = array_map('trim',$_POST);
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
$projects_html = "<option></option>";
foreach ($projects as $project) {
	$projects_html .= "<option value='" . $project['project_id'] . "'>";
	$projects_html .= $project['project_name'] . "</option>";
	
	
}

require_once 'includes/header.inc.php';
?>
<h3>Add Data Directory</h3>
<hr>
<div class='col-sm-6 col-md-6 col-lg-6 col-xl-6'>
<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>' name='form'>
	<div class='form-group row'>
		<label class='col-sm-4 col-form-label' for='directory_input'>Directory:</label>
		<div class='col-sm-8'>
			<input class='form-control' type='text' name='directory' id='directory_input'
				value='<?php if (isset($_POST['directory'])) { echo $_POST['directory']; } ?>'>
		</div>
	</div>
	<div class='form-group row'>
		<label class='col-sm-4 col-form-label' for='project_input'>Project:</label>
		<div class='col-sm-8'>
		<select class='custom-select' name='project_id' id='project_input'>
			<?php echo $projects_html; ?>
		</select>
		</div>
	</div>
	<div class='form-group row'>
			<input class='btn btn-primary' type='submit' name='add_dir' value='Add Directory'>&nbsp; 
			<input class='btn btn-warning' type='submit' name='cancel_dir' value='Cancel'>
	</div>
</form>
</div>

<?php
if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; }
?>
</div>

<?php 
require_once 'includes/footer.inc.php';
?>

<script type='text/javascript'>
$(document).ready(function() {
	$('#project_input').select2({
        	'placeholder': "Select a Project"
	});
});

</script>

