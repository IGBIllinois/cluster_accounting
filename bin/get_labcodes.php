#!/usr/bin/env php
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

require_once '../conf/app.inc.php';
require_once '../conf/settings.inc.php';
require_once '../vendor/autoload.php';

date_default_timezone_set(settings::get_timezone());

//Command parameters
$output_command = "update_labcodes.php Updates Labcodes against FBS rest api\n";
$output_command .= "Usage: php data.php \n";
$output_command .= "	--dry-run	Do dry run, do not submit\n";
$output_command .= "	--email		Sends email to admin email address with report\n";
$output_command .= "    -h, --help              Display help menu\n";

//Parameters
$shortopts = "h";

$longopts = array(
	"dry-run",
	"email",
        "help"
);

//Following code is to test if the script is being run from the command line or the apache server.
$sapi_type = php_sapi_name();
if ($sapi_type != 'cli') {
	exit("Error: This script can only be run from the command line.");
}

$send_email = false;
$dryrun = false;

$options = getopt($shortopts,$longopts);

if (isset($options['h']) || isset($options['help'])) {
	echo $output_command;
	exit;
}

if (isset($options['dry-run'])) {
	$dryrun = true;
}
if (isset($options['email'])) {
	$send_email = true;
}


$db = new \IGBIllinois\db(settings::get_mysql_host(),
	settings::get_mysql_database(),
	settings::get_mysql_user(),
	settings::get_mysql_password(),
	settings::get_mysql_ssl(),
	settings::get_mysql_port()
);

$fbs = new \IGBIllinois\fbs(settings::get_fbs_access_key(),settings::get_fbs_secret_key());
$token = $fbs->login();
$customers = $fbs->get_customers(settings::get_fbs_facility_id());
print_r($customers);


?>
