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


$db = new \IGBIllinois\db(settings::get_mysql_host(),
			settings::get_mysql_database(),
			settings::get_mysql_user(),
			settings::get_mysql_password(),
			settings::get_mysql_ssl(),
			settings::get_mysql_port()
			);

//$ldap = new \IGBIllinois\ldap(__LDAP_HOST__,__LDAP_BASE_DN__,__LDAP_PORT__,__LDAP_SSL__,__LDAP_TLS__);

$session = new \IGBIllinois\session(settings::get_session_name());
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
		$message .= "<div class='alert alert-danger'>Please enter your username.</div>";
	}
	if ($password == "") {
		$error = true;
		$message .= "<div class='alert alert-danger'>Please enter your password.</div>";
	}
	if ($error == false) {
		$ldap = new \IGBIllinois\ldap(settings::get_ldap_host(),
			settings::get_ldap_base_dn(),
			settings::get_ldap_port(),
			settings::get_ldap_ssl(),
			settings::get_ldap_tls());
		if (settings::get_ldap_bind_user() != "") {
			$ldap->bind(settings::get_ldap_bind_user(),settings::get_ldap_bind_password());
		}
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
			$message .= "<div class='alert alert-danger'>Invalid username or password.  Please try again. </div>";
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
<title><?php echo settings::get_title(); ?></title>
</head>
<body class='d-flex flex-column min-vh-100' OnLoad="document.login.username.focus();" style='padding-top: 70px; padding-bottom: 60px;'>
<nav class='navbar fixed-top navbar-dark bg-dark'>
	<div class='container-fluid'>
	<a class="navbar-brand py-0" href="#"><?php echo settings::get_title(); ?></a>
	<span class="navbar-text py-0">Version <?php echo settings::get_version(); ?></span>
	</div>
</nav>
<br>
	
<div class='container'>
	<div class='row'>
		<div class='col-sm-10 col-md-10 col-lg-10 col-xl-10'>
			<div class='container text-sm-left p-5 bg-light'>
        			<h1 class='display-4'>
	        	        <img src="images/imark_bw.gif"
        	        	        style="padding: 0 10px 10px 0; vertical-align: text-top;">Biocluster
	                	Accounting
		        	</h1>
        			<p class='lead'>View, manage, and bill Biocluster usage and storage</p>
			</div>
		</div>
	</div>
	<br>
	<div class='row'>
		<div class='col-sm-10 col-md-10 col-lg-10 col-xl-10'>
		<div class='card'>
			<div class='card-header bg-light'>Login</div>
		<div class='card-body'>
		<form class='form' role='form' action='login.php' method='post' name='login'>
			<div class='col-sm-10 col-md-10 col-lg-10 col-xl-10 offset-sm-1 offset-md-1 offset-lg-1 offset-xl-1'>
			<div class='form-group'>	
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
			<div class='form-group'>
				<label class='col-form-label' for='password'>Password:</label>
				<div class='input-group'>
					<input class='form-control' type='password' name='password' 
						placeholder='Password' tabindex='2'>
					<div class='input-group-append'>
						<span class='input-group-text'><i class='fas fa-lock'></i></span>
					</div>

				</div>
			</div>
			<br>		
			<div class='form-group'>
				
				<button type='submit' name='login' class='btn btn-primary'>Login</button>
				<div class='float-end'><?php if (settings::get_password_reset_url()) {
					echo "<a class='pull-right' target='_blank' href='" . settings::get_password_reset_url() . "'>Forgot Password?</a>";
				}
				?>
				</div>
			</div>
			</div>
		</form>
		</div>
		</div>
		</div>
	</div>
	<br>
	<div class='row'>
		<div class='col-sm-10 col-md-10 col-lg-10 col-xl-10'>
		<?php if (isset($message)) { echo $message; } ?>
		</div>
	</div>
</div>

<?php require_once 'includes/footer.inc.php'; ?>
