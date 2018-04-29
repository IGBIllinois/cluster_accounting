<?php
require_once 'includes/header.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

//Create New Queue
if (isset($_POST['new_queue'])) {
        foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }
        $queue = new queue($db);
        $result = $queue->create($_POST['name'],$_POST['description'],$_POST['ldap_group'],$_POST['cpu_cost'],$_POST['mem_cost'],$_POST['gpu_cost']);
        if($result['RESULT']) {
                unset($_POST);
        }
}

elseif (isset($_POST['cancel_queue'])) {
        unset($_POST);

}


?>

<form class='form-horizontal' method='post'
        action='<?php echo $_SERVER['PHP_SELF']; ?>'>
        <fieldset>
                <legend>Add Queue</legend>
                <div class='control-group'>
                        <label class='control-label' for='name_input'>Queue Name:</label>
                        <div class='controls'>
                                <input type='text' class='input-medium' id='name_input' name='name'
                                        value='<?php if (isset($_POST['name'])) { echo $_POST['name']; }?>'>
                        </div>
                </div>
                <div class='control-group'>
                        <label class='control-label' for='description_input'>Description:</label>
                        <div class='controls'>
                                <input class='input-medium' type='text' name='description'
                                        id='description_input'
                                        value='<?php if (isset($_POST['description'])) { echo $_POST['description']; } ?>'>
                        </div>

                </div>
                <div class='control-group'>
                        <label class='control-label' for='ldap_group_input'>LDAP Group
                                (optional):</label>
                        <div class='controls'>
                                <input class='input-medium' type='text' name='ldap_group'
                                        id='ldap_group_input'
                                        value='<?php if (isset($_POST['ldap_group'])) { echo $_POST['ldap_group']; } ?>'>
                        </div>
                </div>
                <div class='control-group'>
                        <label class='control-label' for='cpu_input'>CPU Cost (per second): </label>
                        <div class='controls'>
                                <input class='input-medium' type='text' name='cpu_cost'
                                        id='cpu_input'
                                        value='<?php if (isset($_POST['cpu_cost'])) { echo $_POST['cpu_cost']; } ?>'>
                        </div>
                </div>
                <div class='control-group'>
                        <label class='control-label' for='mem_input'>Memory Cost (per
                                gigabyte): </label>
                        <div class='controls'>
                                <input class='input-medium' type='text' name='mem_cost'
                                        id='mem_input'
                                        value='<?php if (isset($_POST['mem_cost'])) { echo $_POST['mem_cost']; } ?>'>
                        </div>
                </div>
                <div class='control-group'>
                        <label class='control-label' for='gpu_input'>GPU Cost (per second): </label>
                        <div class='controls'>
                                <input class='input-medium' type='text' name='gpu_cost'
                                        id='gpu_input'
                                        value='<?php if (isset($_POST['gpu_cost'])) { echo $_POST['gpu_cost']; } ?>'>
                        </div>
                </div>
                <div class='control-group'>
                        <div class='controls'>
                                <input class='btn btn-primary' type='submit' name='new_queue'
                                        value='Create Queue'> <input class='btn btn-warning' type='submit'
                                        name='cancel_queue' value='Cancel'>
                        </div>
                </div>
        </fieldset>
</form>
<?php

if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; }


require_once 'includes/footer.inc.php';
?>
