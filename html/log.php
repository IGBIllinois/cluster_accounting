<?php
require_once 'includes/main.inc.php';
if (!$login_user->is_admin()) {
        exit;
}

$log_contents = $log->get_log();

require_once 'includes/header.inc.php';

?>

<h3>View Log</h3>
<hr>
<textarea class='form-control' rows='50' readonly><?php echo $log_contents; ?></textarea>

</div>
<?php

require_once 'includes/footer.inc.php';
?>
