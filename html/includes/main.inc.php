<?php

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

require_once 'includes/session.inc.php';
?>
