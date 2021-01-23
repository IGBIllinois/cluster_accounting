<?php
//////////////////////////////////////////
//					
//	logout.php			
//
//	Logs user out
//
//	By: David Slater
//	Date: May 2009
//
//////////////////////////////////////////

$include_paths = array('../libs');

set_include_path(get_include_path() . ":" . implode(':',$include_paths));
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
$session->destroy_session();
header("Location: login.php")

?>
