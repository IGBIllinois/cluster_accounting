<?php
require_once 'includes/main.inc.php';
if (!$login_user->is_admin()) {
        exit;
}

$log_contents = $log->get_log();

require_once 'includes/header.inc.php';

?>

<h4>Log</h4>
<textarea class='form-control' rows='50' readonly><?php echo $log_contents; ?></textarea>

<?php

require_once 'includes/footer.inc.php';
?>
