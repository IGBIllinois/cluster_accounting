<?php

require_once 'includes/main.inc.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel='stylesheet' href='vendor/components/jquery-ui/themes/base/jquery-ui.css'>
<script src='includes/main.inc.js' type='text/javascript'></script>
<script src='vendor/components/jquery/jquery.min.js' type='text/javascript'></script>
<script src='vendor/components/jquery-ui/ui/minified/jquery-ui.min.js' type='text/javascript'></script>
<script src='vendor/components/bootstrap/js/bootstrap.min.js' type='text/javascript'></script>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css"
	href="vendor/components/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css"
        href="vendor/components/bootstrap/css/bootstrap-responsive.css">
<title><?php echo __TITLE__; ?></title>

</head>

<body>
	<div class='navbar navbar-inverse'>
		<div class='navbar-inner'>
			<div class='container'>
				<div class='brand'>
					<?php echo __TITLE__; ?>
				</div>
				<span class='navbar-text pull-right'>Version <?php echo __VERSION__; ?>&nbsp;
					<a class='btn btn-danger btn-small' role="button" href='logout.php'>Logout</a>
				</span>
			</div>
		</div>
	</div>

	<div class='container-fluid'>
		<div class='row-fluid'>
			<div class='span2'>
				<div class='sidebar-nav'>
					<ul class='nav nav-tabs nav-stacked'>

						<li><a href='index.php'>Main</a></li>
						<li><a href='user.php'>Information</a></li>
						<li><a href='user_bill.php'>User Bill</a></li>
						<li><a href='jobs.php'>Jobs</a></li>
						<li><a href='user_graphs.php'>User Graphs</a></li>
						<?php	
						if ((isset($login_user)) && ($login_user->is_admin())) {
							echo "<li><a href='job_billing.php'>Job Billing</a></li>";
							echo "<li><a href='data_billing.php'>Data Billing</a></li>";
							echo "<li><a href='stats_accumulated.php'>Accumulated Stats</a></li>";
							echo "<li><a href='stats_monthly.php'>Monthly Stats</a></li>";
							echo "<li><a href='stats_yearly.php'>Yearly Stats</a></li>";
							echo "<li><a href='stats_fiscal.php'>Fiscal Stats</a></li>";
							echo "<li><a href='list_users.php'>List Users</a></li>";
							echo "<li><a href='add_user.php'>Add User</a></li>";
							echo "<li><a href='projects.php'>Projects</a></li>";
							echo "<li><a href='add_project.php'>Add Project</a></li>";
							echo "<li><a href='queues.php'>Queues</a></li>";
							echo "<li><a href='add_queue.php'>Add Queue</a></li>";
							echo "<li><a href='data_dir_home.php'>Home Directories</a></li>";
							echo "<li><a href='data_dir_custom.php'>Custom Data Directories</a></li>";
							echo "<li><a href='add_data_dir.php'>Add Data Directory</a></li>";
							echo "<li><a href='data_cost.php'>Data Cost</a></li>";
							echo "<li><a href='verification.php'>Verify Users and Directories</a></li>";
							
						}
						?>
						<li><a href='about.php'>About</a></li>
					</ul>
				</div>
			</div>
			<div class='span10'>
