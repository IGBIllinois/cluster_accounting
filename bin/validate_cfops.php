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

require_once '../conf/settings.inc.php';
require_once '../vendor/autoload.php';

date_default_timezone_set(settings::get_timezone());

//Command parameters
$output_command = "validate_cfops.php Validates active CFOPs against Banner Database\n";
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

$log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_logfile());
	
$start_time = microtime(true);
$log->send_log("Validate CFOPs: Start");	

$cfops = functions::get_all_cfops($db);
$cfop_obj =  new \IGBIllinois\cfop(settings::get_cfop_api_key(),settings::get_debug());
$txt_message = "Invalid CFOPS\n\n";
foreach ($cfops as $cfop) {
	if (!$dryrun) {
		try {
	        	$result = $cfop_obj->validate_cfop($cfop['cfop'],$cfop['activity_code']);
		}
		catch (\Exception $e) {
			$message = $cfop['cfop'];
			if ($cfop['activity_code'] != "") {
				$message .= " - " . $cfop['activity_code'];
			}
	        	echo $message . " " . $e->getMessage() . "\n";
			$txt_message .= $message . " " . $e->getMessage() . "\n";
		}
	}
}

if ($send_email) {
	
	$subject = "Biocluster - Invalid CFOPS";

	$email = new \IGBIllinois\email(settings::get_smtp_host(),
                                                settings::get_smtp_port(),
                                                settings::get_smtp_username(),
                                                settings::get_smtp_password());
	$email->set_to_emails(settings::get_admin_email());
	try {
		echo $txt_message;
		$result = $email->send_email(settings::get_from_email(),$subject,$txt_message,"",settings::get_from_name());
		$message = "Email Invalid CFOPS successfully sent to " . settings::get_admin_email();
		echo $message;

	}
	catch (Exception $e) {
		$message = "Email Invalid CFOPS: Error sending mail. " . $e->getMessage();
		echo $message;
	}



}

$end_time = microtime(true);
$elapsed_time = round($end_time - $start_time,2);
$log->send_log("Validate CFOPs: Finished. Elapsed Time: " . $elapsed_time . " seconds");

?>
