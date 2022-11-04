<?php

require_once 'includes/main.inc.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<script src='vendor/components/jquery/jquery.min.js' type='text/javascript'></script>
<script src='vendor/components/jqueryui/jquery-ui.min.js' type='text/javascript'></script>
<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
<script src='vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js' type='text/javascript'></script>
<script type="text/javascript" src='vendor/select2/select2/dist/js/select2.min.js'></script>
<script src='includes/main.inc.js' type='text/javascript'></script>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">

<link rel='stylesheet' href='vendor/components/jqueryui/themes/base/jquery-ui.css'>
<link rel="stylesheet" type="text/css" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="vendor/fortawesome/font-awesome/css/all.min.css">
<link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" type="text/css" />
<link rel="stylesheet" href="vendor/ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css" type="text/css" />
<title><?php echo settings::get_title(); ?></title>

</head>

<body class='d-flex flex-column min-vh-100' style='padding-top: 70px; padding-bottom: 60px;'>
<nav class='navbar fixed-top navbar-dark bg-dark'>
	<a class='navbar-brand py-0' href='#'><?php echo settings::get_title(); ?></a>
	<span class='navbar-text py-0'>Version <?php echo settings::get_version(); ?>&nbsp;
		<a class='btn btn-danger btn-sm' role="button" href='logout.php'>Logout</a>
	</span>
</nav>
<p>
<div class='container-fluid'>
	<div class='row'>
			<div class='col-sm-2 col-md-2 col-lg-2 col-xl-2'>
					<ul class='nav flex-column'>

						<li class='nav-item'><a class='nav-link' href='index.php'>Main</a></li>
						<li class='nav-item'><a class='nav-link' href='user.php'>Information</a></li>
						<li class='nav-item'><a class='nav-link' href='user_bill.php'>User Bill</a></li>
						<li class='nav-item'><a class='nav-link' href='jobs.php'>Jobs</a></li>
						<li class='nav-item'><a class='nav-link' href='user_graphs.php'>User Graphs</a></li>
						<?php	
						if ((isset($login_user)) && ($login_user->is_admin())) {
							echo "<span class='border-top my-2'></span>";
							echo "<li class='nav-item'><a class='nav-link' href='job_billing.php'>Job Billing</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='data_billing.php'>Data Billing</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='stats_accumulated.php'>Accumulated Stats</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='stats_monthly.php'>Monthly Stats</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='stats_yearly.php'>Yearly Stats</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='stats_fiscal.php'>Fiscal Stats</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='list_users.php'>List Users</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='add_user.php'>Add User</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='projects.php'>Projects</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='add_project.php'>Add Project</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='queues.php'>Queues</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='add_queue.php'>Add Queue</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='data_dir_home.php'>Home Directories</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='data_dir_custom.php'>Custom Data Directories</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='add_data_dir.php'>Add Data Directory</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='data_cost.php'>Data Cost</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='verification.php'>Verify Users and Directories</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='log.php'>View Log</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href='about.php'>About</a></li>";
							
						}
						?>

					</ul>
			</div>
			<div class='col-sm-9 col-md-9 col-lg-9 col-xl-9'>
