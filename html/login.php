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

$include_paths = array('../libs');

set_include_path(get_include_path() . ":" . implode(':',$include_paths));
require_once '../conf/app.inc.php';
require_once '../conf/settings.inc.php';
require_once '../vendor/autoload.php';

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');

$db = new \IGBIllinois\db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
$ldap = new \IGBIllinois\ldap(__LDAP_HOST__,__LDAP_BASE_DN__,__LDAP_PORT__,__LDAP_SSL__,__LDAP_TLS__);

$session = new \IGBIllinois\session(__SESSION_NAME__);
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
		$ldap = new \IGBIllinois\ldap(__LDAP_HOST__,__LDAP_BASE_DN__,__LDAP_PORT__,__LDAP_SSL__);
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
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="vendor/fortawesome/font-awesome/css/all.min.css">
<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js" type="text/javascript"></script>
<title><?php echo __TITLE__; ?></title>
</head>
<body OnLoad="document.login.username.focus();" style='padding-top: 70px; padding-bottom: 60px;'>
<nav class='navbar fixed-top navbar-dark bg-dark'>
	<a class="navbar-brand py-0" href="#"><?php echo __TITLE__; ?></a>
	<span class="navbar-text py-0">Version <?php echo __VERSION__; ?></span>
</nav>
	<p>
	
<div class='container'>
	<div class='col-md-6 col-lg-6 col-xl-6 offset-md-3 offset-lg-3 offset-xl-3'>

		<form class='form' role='form' action='login.php' method='post' name='login'>
			<div class='form-group-row'>	
				<label class='col-form-label' for='username'>Username:</label> 
				<div class='input-group'>
					<input class='form-control' type='text'
					name='username' tabindex='1' placeholder='Username'
					value='<?php if (isset($username)) { echo $username; } ?>' autocapitalize='none'> 
					<div class='input-group-append'>
						<span class='input-group-text'><i class='fas fa-user'></i></span>
					</div>
				</div>
			</div>
			<div class='form-group-row'>
				<label class='col-form-label' for='password'>Password:</label>
				<div class='input-group'>
					<input class='form-control' type='password' name='password' 
						placeholder='Password' tabindex='2'>
					<div class='input-group-append'>
						<span class='input-group-text'><i class='fas fa-lock'></i></span>
					</div>

				</div>
			</div>
					
			<div class='form-group-row'>
				<button type='submit' name='login' class='btn btn-primary'>Login</button>
				<?php if (settings::get_password_reset_url()) {
					echo "<a class='pull-right' href='" . settings::get_password_reset_url() . "'>Forgot Password?</a>";
				}
				?>
			</div>
		</form>

		<p>
		<?php if (isset($message)) { echo $message; } ?>
	</div>
</div>
<footer class='footer'>
	<div class='container'>
		<p class='text-center'>
        	<br><em>Computer & Network Resource Group - Carl R. Woese Institute for Genomic Biology</em>
	        <br><span class='text-muted'><strong><em>If you have any questions, please email us at <a href='help@igb.illinois.edu'>help@igb.illinois.edu</a></em></strong>
        	<br><em><a href='https://www.vpaa.uillinois.edu/resources/web_privacy'>University of Illinois System Web Privacy Notice</a></em>
	        <br><em>&copy 2012-<?php echo date('Y'); ?> University of Illinois Board of Trustees</em>
		</p>
	</div>
</footer>

