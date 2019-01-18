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

$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);

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
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css"
        href="vendor/components/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css"
        href="vendor/components/bootstrap/css/bootstrap-responsive.css">
<title><?php echo __TITLE__; ?></title>
</head>
<body OnLoad="document.login.username.focus();">
	<div class='navbar navbar-inverse'>
		<div class='navbar-inner'>
			<div class='container'>
				<a class="btn btn-navbar" data-toggle="collapse"
					data-target=".nav-collapse"></a> <a class="brand" href="#"><?php echo __TITLE__; ?>
				</a>
				<p class='navbar-text pull-right'>
                                                Version <?php echo __VERSION__; ?>
				</p>
			</div>
		</div>
	</div>
	<p>
	
	
	<div class='container'>
			<div class='span6 offset3'>

				<form action='login.php' method='post' name='login'
					class='form-vertical'>
					<div class='control-group'>	
					<label class='control-label' for='username'>Username:</label> 
					<div class='controls'>
						<div class='input-append'>
						<input class='span5' type='text'
						name='username' tabindex='1' placeholder='Username'
						value='<?php if (isset($username)) { echo $username; } ?>'> 
						<span class='add-on'><i class='icon-user'></i></span>
						</div>
						</div>
					</div>
					<div class='control-group'>
					<label class='control-label' for='password'>Password:</label>
					<div class='controls'>
						<div class='input-append'>
					<input class='span5' type='password' name='password' 
						placeholder='Password' tabindex='2'>
							<span class='add-on'><i class='icon-lock'></i></span>
						</div>

					</div>
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
