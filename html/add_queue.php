<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

//Create New Queue
if (isset($_POST['new_queue'])) {
        foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }
        $queue = new queue($db);
        $result = $queue->create($_POST['name'],$_POST['description'],$_POST['skucode'],$_POST['ldap_group'],$_POST['cpu_cost'],$_POST['mem_cost'],$_POST['gpu_cost'],$ldap);
        if($result['RESULT']) {
                unset($_POST);
        }
}

elseif (isset($_POST['cancel_queue'])) {
        unset($_POST);

}

require_once 'includes/header.inc.php';
?>
<h3>Add Queue</h3>
<hr>
<div class='col-sm-12 col-md-12 col-lg-12 col-xl-12'>

<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='name_input'>Queue Name:</label>
		<div class='col-sm-2'>
		<input class='form-control' type='text' id='name_input' name='name'
			value='<?php if (isset($_POST['name'])) { echo $_POST['name']; }?>'>
		</div>
	</div>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='description_input'>Description:</label>
		<div class='col-sm-2'>
		<input class='form-control' type='text' name='description' id='description_input'
			value='<?php if (isset($_POST['description'])) { echo $_POST['description']; } ?>'>
		</div>
	</div>
	<div class='mb-3 row'>
                <label class='col-sm-2 form-label' for='description_input'>FBS SKU Code (optional):</label>
		<div class='col-sm-2'>
                <input class='form-control' type='text' name='skucode' id='skucode_input'
                        value='<?php if (isset($_POST['skucode'])) { echo $_POST['skucode']; } ?>'>
		</div>
        </div>

	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='ldap_group_input'>LDAP Group (optional):</label>
		<div class='col-sm-2'>
		<input class='form-control' type='text' name='ldap_group' id='ldap_group_input'
			value='<?php if (isset($_POST['ldap_group'])) { echo $_POST['ldap_group']; } ?>'>
		</div>
	</div>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='cpu_input'>CPU Cost (per second): </label>
		<div class='col-sm-2'>
		<div class='input-group'>
			<span class='input-group-text'>$</span>
			<input class='form-control' type='text' name='cpu_cost' id='cpu_input'
			value='<?php if (isset($_POST['cpu_cost'])) { echo $_POST['cpu_cost']; } ?>' placeholder='0.00'>
		</div>
		</div>
	</div>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='mem_input'>Memory Cost (per gigabyte): </label>
		<div class='col-sm-2'>
		<div class='input-group'>
			<span class='input-group-text'>$</span>
		<input class='form-control' type='text' name='mem_cost' id='mem_input'
			value='<?php if (isset($_POST['mem_cost'])) { echo $_POST['mem_cost']; } ?>' placeholder='0.00'>
		</div>
		</div>
	</div>
	<div class='mb-3 row'>
		<label class='col-sm-2 form-label' for='gpu_input'>GPU Cost (per second): </label>
		<div class='col-sm-2'>
		<div class='input-group'><span class='input-group-text'>$</span>
		<input class='form-control' type='text' name='gpu_cost' id='gpu_input'
			value='<?php if (isset($_POST['gpu_cost'])) { echo $_POST['gpu_cost']; } ?>' placeholder='0.00'>
		</div>
		</div>
	</div>
	<div class='mb-3 row'>
		<div class='col-sm-8'>
			<input class='btn btn-primary' type='submit' name='new_queue' value='Create Queue'>
			<input class='btn btn-warning' type='submit' name='cancel_queue' value='Cancel'>
		</div>
	</div>
</form>
<?php

if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; }
?>
</div>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
