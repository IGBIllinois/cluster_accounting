<?php

$include_paths = array('../libs',
		'includes/jpgraph-3.5.0b1/src',
		'includes/PHPExcel_1.8.0/Classes');

set_include_path(get_include_path() . ":" . implode(':',$include_paths));
include_once '../conf/settings.inc.php';
function my_autoloader($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

spl_autoload_register('my_autoloader');

$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
?>
