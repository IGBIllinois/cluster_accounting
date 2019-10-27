<?php
require_once 'includes/header.inc.php';

$user_id = 0;
$user = "";
if (isset($_GET['user_id']) && (is_numeric($_GET['user_id']))) {
	$user = new user($db,$ldap,$_GET['user_id']);
}
elseif (isset($_GET['username'])) {
	$user = new user($db,$ldap,'',$_GET['username']);
}
else {
	$user = new user($db,$ldap,$login_user->get_user_id());
}
if (!$login_user->permission($user_id)) {
        echo "<div class='alert alert-error'>Invalid Permissions</div>";
        exit;
}

$supervising_users_html = "";
if ($user->is_supervisor()) {
	$supervising_users = $user->get_supervising_users();
	if (count($supervising_users)) {
		for ($i=0;$i<count($supervising_users);$i++) {
			$element = "<td><a href='user.php?user_id=" . $supervising_users[$i]['user_id'] . "'>";
	        	$element .= $supervising_users[$i]['user_name'] . "</a></td>";
			if ($i % 2 == 0) {
	        	        if ($i == count($supervising_users) - 1) {
	                	        $supervising_users_html .= "<tr> " . $element . "<td></td></tr>";
	                	}
				else {
					$supervising_users_html .= "<tr>" . $element;
				}
			}
			elseif ($i % 2 == 1) {
				$supervising_users_html .= $element  . "</tr>\n";
			}


		}
	}
}

$projects = $user->get_projects();
$projects_html = "";
foreach ($projects as $project) {
	if ($project['project_default']) {
		$projects_html .= "<tr><td></td><td>" . $project['project_name'] . " - default project</td></tr>";
	}
	else {
		$projects_html .= "<tr><td></td><td>" . $project['project_name'] . "</td></tr>";
	}


}

$queues = $user->get_queues();
$queues_html = "";
foreach ($queues as $queue) {
	$queues_html .= "<tr><td></td><td>" . $queue . "</td></tr>";

}



?>
<div class='row'>
<table class='table table-striped table-bordered table-sm'>
	<tr>
		<td>Name:</td>
		<td><?php echo $user->get_full_name(); ?></td>
	</tr>
	<tr>
		<td>Username:</td>
		<td><?php echo $user->get_username(); ?></td>
	</tr>
	<tr>
		<td>Email:</td>
		<td><?php echo $user->get_email(); ?></td>
	</tr>
	<tr>
                <td>Time Created:</td>
                <td><?php echo $user->get_time_created(); ?></td>
        </tr>

	<tr>
		<td>Administrator:</td>
		<td><?php if ($user->is_admin()) {
                	echo "<i class='icon-ok'></i>";
        	}
        	else {
                	echo "<i class='icon-remove'></i>";
        	}
		?>
		</td>
	</tr>
	<tr>	<td>Active IGB/LDAP Account</td>
		<td><?php if ($ldap->is_ldap_user($user->get_username())) {
			echo "<i class='icon-ok'></i>";
		}
		else {
			echo "<i class='icon-remove'></i>";
		}
		?></td></tr>
		<?php if ($user->is_supervisor()) {    
                        echo "<tr><td>Is Supervisor:</td><td><i class='icon-ok'></i></td></tr>";
                }
                else {
                        echo "<tr><td>Is Supervisor:</td><td><i class='icon-remove'></i></td></tr>";
			echo "<tr><td>Supervisor's Name: </td><td>" . $user->get_supervisor_name() . "</td></tr>";
                }
                ?>

	<tr><td colspan='2'>Projects:</td><tr>
		<?php echo $projects_html; ?>

	<tr>
		<td colspan='2'>Queues:</td>
	</tr>
	<?php echo $queues_html; ?>

	<?php
	if ($user->is_supervisor()) {
		echo "<tr><td colspan='2'>Supervising Users:</td></tr>";
		echo $supervising_users_html;

	}


	?>
</table>
</div>
<div class='row'>
<?php 

if ($login_user->is_admin()) {
	echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?user_id=" . $user->get_user_id() . "'>";
	echo "<div class='btn-toolbar'>";
	echo "<div class='btn-group'>";
	echo "<a class='btn btn-primary' href='edit_project.php?project_id=" .
			$user->default_project()->get_project_id() . "'>";
	echo "<i class='icon-pencil'></i>Edit User Project</a>";
	echo "<a class='btn btn-primary' href='edit_user.php?user_id=" . $user->get_user_id() . "'><i class='icon-pencil'></i>Edit User</a>";
	echo "<a class='btn btn-info' href='user_bill.php?user_id=" . $user->get_user_id() . "'>User Bill</a>";
	echo "<a class='btn btn-success' href='jobs.php?user_id=" . $user->get_user_id() . "'>User Jobs</a>";
	echo "</div></div>";
	echo "</form>";
}

if (isset($result['MESSAGE'])) { 
	echo "<div class='alert alert-error'>" . $result['MESSAGE'] . "</div>"; 
}
?>
</div>
<?php 
require_once 'includes/footer.inc.php'; 

?>
