<?php
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

include_once '../conf/settings.inc.php';


//Following code is to test if the script is being run from the command line or the apache server.
$sapi_type = php_sapi_name();
if ($sapi_type != 'cli') {
        echo "Error: This script can only be run from the command line.";
}
else {

	$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
	$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	
	$user_list = user_functions::get_users($db,$ldap);
	print_r($user_list);
	foreach ($user_list as $user) {
			$user_object = new user($db,$ldap,$user['user_id']);
			$user_object->email_bill(__ADMIN_EMAIL__);
	}


}









?>
