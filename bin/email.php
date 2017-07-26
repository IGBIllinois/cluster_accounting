<?php
chdir(dirname(__FILE__));

$include_paths = array('../libs');
set_include_path(get_include_path() . ":" . implode(':',$include_paths));

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}
spl_autoload_register('my_autoloader');

require_once '../conf/settings.inc.php';
require_once '../vendor/autoload.php';

//Command parameters
$output_command = "email.php Emails users their monthly bill\n";
$output_command .= "Usage: php email.php \n";
$output_command .= "	--year			Year (YYYY) (Default: Current Year)\n";
$output_command .= "	--month			Month (MM) (Default: Previous Month)\n";
$output_command .= "	-h, --help              Display help menu\n";

//Parameters
$shortopts = "h";

$longopts = array(
        "help",
	"year::",
	"month::"
);


//Following code is to test if the script is being run from the command line or the apache server.
if (php_sapi_name() != 'cli') {
        echo "Error: This script can only be run from the command line.";
}
else {
	$year = "";
	$month = "";
	$options = getopt($shortopts,$longopts);

	print_r($options);
        if (isset($options['h']) || isset($options['help'])) {
                echo $output_command;
                exit;
        }

	//$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
	//$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	
	//$user_list = user_functions::get_users($db,$ldap);
	//foreach ($user_list as $user) {
	//		$user_object = new user($db,$ldap,$user['user_id']);
	//		$user_object->email_bill(__ADMIN_EMAIL__);
	//}


}









?>
