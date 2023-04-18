<?php
//////////////////////////////////////////////////
//						//
//	session.inc.php				//
//						//
//	Used to verify the user is		// 
//	logged in before proceeding		//
//						//
//	David Slater				//
//	May 2009				//
//						//
//////////////////////////////////////////////////

$session = new \IGBIllinois\session(settings::get_session_name());
$login_user;
//If not logged in
if (!($session->get_var('login'))) {
	$webpage = $_SERVER['PHP_SELF'];
	if ($_SERVER['QUERY_STRING'] != "") {
		$webpage .= "?" . $_SERVER['QUERY_STRING'];
	}
	$session->set_session_var('webpage',$webpage);

	header('Location: login.php');
	exit();
}
//If session timeout is reach
elseif (time() > $session->get_var('timeout') + settings::get_session_timeout()) {
	header('Location: logout.php');
}
//If IP address is different
elseif ($_SERVER['REMOTE_ADDR'] != $session->get_var('ipaddress')) {
        header('Location: logout.php');
}

else {
	$login_user = new user($db,$ldap,0,$session->get_var('username'));	
	//Reset Timeout
	$session_vars = array('timeout'=>time());
	$session->set_session($session_vars);
}
?>
