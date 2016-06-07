<?php
///////////////////////////////////
//
//	login.php
//
//
//	David Slater
//	May 2009
//
///////////////////////////////////

include_once 'includes/main.inc.php';

$session = new session(__SESSION_NAME__);
$message = "";
$webpage = $dir = dirname($_SERVER['PHP_SELF']) . "/index.php";
if ($session->get_var('webpage') != "") {
	$webpage = $session->get_var('webpage');
}

if (isset($_POST['login'])) {

	$username = trim(rtrim($_POST['username']));
	$password = $_POST['password'];

	$error = false;
	if ($username == "") {
		$error = true;
		$message .= "<div class='alert'>Please enter your username.</div>";
	}
	if ($password == "") {
		$error = true;
		$message .= "<div class='alert'>Please enter your password.</div>";
	}
	if ($error == false) {
		$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
		$login_user = new user($db,$ldap,0,$username);
		$success = $login_user->authenticate($password);
		if ($success) {
			$session_vars = array('login'=>true,
                        'username'=>$username,
                        'timeout'=>time(),
                        'ipaddress'=>$_SERVER['REMOTE_ADDR']
                	);
	                $session->set_session($session_vars);


        	        $location = "http://" . $_SERVER['SERVER_NAME'] . $webpage;
                	header("Location: " . $location);

		}
		else {
			$message .= "<div class='alert'>Invalid username or password.  Please try again. </div>";
		}
	}
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css"
	href="includes/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css"
	href="includes/bootstrap/css/bootstrap-responsive.min.css">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<title><?php echo __TITLE__; ?></title>
</head>
<body OnLoad="document.login.username.focus();">
	<div class='navbar navbar-inverse'>
		<div class='navbar-inner'>
			<div class='container'>
				<a class="btn btn-navbar" data-toggle="collapse"
					data-target=".nav-collapse"></a> <a class="brand" href="#"><?php echo __TITLE__; ?>
				</a>
			</div>
		</div>
	</div>
	<p>
	
	
	<div class='container-fluid'>
		<div class='row'>
			<div class='span6 offset4'>

				<form action='login.php' method='post' name='login'
					class='form-vertical'>
					<label>Username:</label> <input class='span3' type='text'
						name='username' tabindex='1' placeholder='Username'
						value='<?php if (isset($username)) { echo $username; } ?>'> 
					<i class='icon-user'></i>
					<label>Password:</label>
					<div class='controls'>
					<input class='span3' type='password' name='password' 
						placeholder='Password' tabindex='2'><i class='icon-lock'></i>
					</div>
					<br>
					<button type='submit' name='login' class='btn btn-primary'>Login</button>

				</form>


				<?php if (isset($message)) { 
					echo $message;
} ?>

				<em>&copy 2012-<?php echo date('Y'); ?> University of Illinois Board of Trustees</em>
			</div>
		</div>
	</div>
