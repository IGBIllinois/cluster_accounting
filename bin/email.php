<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
	$year = date('Y',strtotime(date('Y-m')." -1 month"));
	$month = date('m',strtotime(date('Y-m')." -1 month"));
	$options = getopt($shortopts,$longopts);

        if (isset($options['h']) || isset($options['help'])) {
                echo $output_command;
                exit;
        }
	if (isset($options['year']) && isset($options['month'])) {
		$year = $options['year'];
		$month = $options['month'];
	}
	elseif ((!isset($options['year']) && isset($options['month'])) ||
		(isset($options['year']) && !isset($options['month']))) {
		echo "Must specify year and month together\n";
		echo $output_command;
		exit;
	}
	$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
	$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);

	$user_list = user_functions::get_users($db,$ldap);
	foreach ($user_list as $user) {
			$user_object = new user($db,$ldap,$user['user_id']);
			$user_object->email_bill(__ADMIN_EMAIL__,$year,$month);
	}


}









?>
