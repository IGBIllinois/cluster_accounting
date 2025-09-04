<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}


$dirs = data_functions::get_unmonitored_dirs($db);

$dirs_html = "";
foreach ($dirs as $dir) {
	$dirs_html .= "<tr><td>" . $dir . "</td></tr>";	
}

$ldap_host_attribute = settings::get_ldap_host_attribute();
$cluster_users = user_functions::get_users_not_in_accounting($db,$ldap,$ldap_host_attribute);

$users_html = "";
foreach ($cluster_users as $cluster_user) {
	$users_html .= "<tr><td>" . $cluster_user . "</td></tr>";
	
}

require_once 'includes/header.inc.php';
?>
<h3>Verify Users and Directories</h3>
<hr>
<div class='row'>
<div class='col-sm-6 col-md-6 col-lg-6 col-xl-6'>
<h4>Users</h4>
<p>These users have access to the biocluster but are not in the accounting program<p>

<table class='table table-striped table-sm table-bordered'>
<?php echo $users_html;  ?>
</table>
</div>
<div class='col-sm-6 col-md-6 col-lg-6 col-xl-6'>
<h4>Directories</h4>
<p>These directories are on the storage system but are not being monitored by the accounting program</p>
<table class='table table-striped table-sm table-bordered'>
<?php echo $dirs_html; ?>
</table>
</div>
</div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
