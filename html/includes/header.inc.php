<?php

require_once 'includes/main.inc.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src='includes/main.inc.js' type='text/javascript'></script>
<script src='vendor/components/jquery/jquery.min.js' type='text/javascript'></script>
<script src='vendor/components/jquery-ui/ui/minified/jquery-ui.min.js' type='text/javascript'></script>
<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

<link rel='stylesheet' href='vendor/components/jquery-ui/themes/base/jquery-ui.css'>
<link rel="stylesheet" type="text/css" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="vendor/fortawesome/font-awesome/css/all.min.css">

<title><?php echo __TITLE__; ?></title>

</head>

<body style='padding-top: 70px; padding-bottom: 60px;'>
<nav class='navbar fixed-top navbar-dark bg-dark'>
	<a class='navbar-brand py-0' href='#'><?php echo __TITLE__; ?></a>
	<span class='navbar-text py-0'>Version <?php echo __VERSION__; ?>&nbsp;
		<a class='btn btn-danger btn-small' role="button" href='logout.php'>Logout</a>
	</span>
</nav>
<p>
<div class='container-fluid'>
	<div class='row'>
			<div class='col-md-2 col-lg-2 col-xl-2'>
				<div class='sidebar-nav'>
					<ul class='nav flex-column'>

						<li class='nav flex-column'><a href='index.php'>Main</a></li>
						<li class='nav flex-column'><a href='user.php'>Information</a></li>
						<li class='nav flex-column'><a href='user_bill.php'>User Bill</a></li>
						<li class='nav flex-column'><a href='jobs.php'>Jobs</a></li>
						<li class='nav flex-column'><a href='user_graphs.php'>User Graphs</a></li>
						<?php	
						if ((isset($login_user)) && ($login_user->is_admin())) {
							echo "<li class='nav flex-column'><a href='job_billing.php'>Job Billing</a></li>";
							echo "<li class='nav flex-column'><a href='data_billing.php'>Data Billing</a></li>";
							echo "<li class='nav flex-column'><a href='stats_accumulated.php'>Accumulated Stats</a></li>";
							echo "<li class='nav flex-column'><a href='stats_monthly.php'>Monthly Stats</a></li>";
							echo "<li class='nav flex-column'><a href='stats_yearly.php'>Yearly Stats</a></li>";
							echo "<li class='nav flex-column'><a href='stats_fiscal.php'>Fiscal Stats</a></li>";
							echo "<li class='nav flex-column'><a href='list_users.php'>List Users</a></li>";
							echo "<li class='nav flex-column'><a href='add_user.php'>Add User</a></li>";
							echo "<li class='nav flex-column'><a href='projects.php'>Projects</a></li>";
							echo "<li class='nav flex-column'><a href='add_project.php'>Add Project</a></li>";
							echo "<li class='nav flex-column'><a href='queues.php'>Queues</a></li>";
							echo "<li class='nav flex-column'><a href='add_queue.php'>Add Queue</a></li>";
							echo "<li class='nav flex-column'><a href='data_dir_home.php'>Home Directories</a></li>";
							echo "<li class='nav flex-column'><a href='data_dir_custom.php'>Custom Data Directories</a></li>";
							echo "<li class='nav flex-column'><a href='add_data_dir.php'>Add Data Directory</a></li>";
							echo "<li class='nav flex-column'><a href='data_cost.php'>Data Cost</a></li>";
							echo "<li class='nav flex-column'><a href='verification.php'>Verify Users and Directories</a></li>";
							
						}
						?>

					</ul>
				</div>
			</div>
			<div class='col-md-10 col-lg-10 col-xl-10'>
