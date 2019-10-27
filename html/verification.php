<?php
require_once 'includes/header.inc.php';

if (!$login_user->is_admin()) {
        exit;
}


$dirs = data_functions::get_unmonitored_dirs($db);

$dirs_html = "";
foreach ($dirs as $dir) {
	$dirs_html .= "<tr><td>" . $dir . "</td></tr>";	
}

$server_name = $_SERVER['SERVER_NAME'];
$cluster_users = user_functions::get_users_not_in_accounting($db,$ldap,$server_name);

$users_html = "";
foreach ($cluster_users as $cluster_user) {
	$users_html .= "<tr><td>" . $cluster_user . "</td></tr>";
	
	
	
}
?>
<h3>Verify Users and Directories</h3>
<div class='row span4'>
<h4>Users</h4>
<p>These users have access to the biocluster but are not in the accounting program<p>

<table class='table table-sm table-bordered'>
<?php echo $users_html;  ?>
</table>
</div>
<div class='row span4'>
<h4>Directories</h4>
<p>These directories are on the storage system but are not being monitored by the accounting program</p>
<table class='table table-sm table-bordered'>
<?php echo $dirs_html; ?>
</table>
</div>

<?php

require_once 'includes/footer.inc.php';
?>
