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
$output_command = "accounting.php - Adds Job Accounting Data to Database. Defaults to today.\n";
$output_command .= "Usage: php accounting.php \n";
$output_command .= "	--start-time		Start Time for jobs (YYYY-MM-DD HH:MM:SS) (Defaults: Today at 00:00:00)\n";
$output_command .= "	--end-time		End Time for jobs (YYYY-MM-DD HH:MM:SS) (Defaults; Now)\n";
$output_command .= "	--previous-hour		Use the previous hour\n";
$output_command .= "	--previous-day 		Use the previous day\n";
$output_command .= "	-h, --help 		Display help menu\n";

//Parameters
$shortopts = "h";

$longopts = array(
	"start-time::",
	"end-time::",
	"previous-hour",
	"previous-day",
	"help"
);

//If not run from command line
if (php_sapi_name() != 'cli') {
	exit("Error: This script can only be run from the command line.\n");
}
	
$start_time = date("Y-m-d 00:00:00");
$end_time = date("Y-m-d H:i:s");
$previous_hour = false;
$previous_day = false;

$options = getopt($shortopts,$longopts);

//verify options are specified correctly
if (isset($options['h']) || isset($options['help'])) {
	echo $output_command;
	exit;
}
elseif (isset($options['previous-hour']) && isset($options['previous-day'])) {
	echo "Must specifiy previous-hour or previous-day.\n";
	exit;
}
elseif ( ((isset($options['previous-hour'])) || (isset($options['previous-day']))) && 
	((isset($options['start-time'])) || (isset($options['end-time']))) ) {
	
	echo "previous-hour and previous-day and not compatible with start-time and end-time\n";
	exit;
}

//start-time is selected
if (isset($options['start-time'])) {
	$start_time = $options['start-time'];
	if (!\IGBIllinois\Helper\date_helper::verify_date($start_time)) {
		exit("Invalid start-time date format.\n");
	}
}

//end-time is selected
if (isset($options['end-time'])) {
	$end_time = $options['end-time'];
	if (!\IGBIllinois\Helper\date_helper::verify_date($end_time)) {
		exit("Invalid end-time date format.\n");
	}

}
					
//previous-hour selected
elseif (isset($options['previous-hour'])) {
	$previous_hour = true;
	$start_time = date('Y-m-d H:00:00',strtotime('-1 hour',time()));
	$end_time = date('Y-m-d H:59:59',strtotime('-1 hour',time()));
}
//previous-day selected
elseif (isset($options['previous-day'])) {
	$previous_day = true;
	$start_time = date('Y-m-d 00:00:00', strtotime('-1 day',time()));
	$end_time = date('Y-m-d 11:59:59', strtotime('-1 day',time()));
}

$start_timestamp = strtotime($start_time);
$end_timestamp = strtotime($end_time);
if ($start_timestamp >= $end_timestamp) {
	exit("start-time is greater than or equal to end-time\n");
}

$db = new \IGBIllinois\db(settings::get_mysql_host(),
                        settings::get_mysql_database(),
                        settings::get_mysql_user(),
                        settings::get_mysql_password(),
                        settings::get_mysql_ssl(),
                        settings::get_mysql_port()
                        );

$ldap = new \IGBIllinois\ldap(settings::get_ldap_host(),
                        settings::get_ldap_base_dn(),
                        settings::get_ldap_port(),
                        settings::get_ldap_ssl(),
                        settings::get_ldap_tls());
if (settings::get_ldap_bind_user() != "") {
        $ldap->bind(settings::get_ldap_bind_user(),settings::get_ldap_bind_password());
}

$log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_logfile());

switch(settings::get_job_scheduler()) {

	case "TORQUE":
		if (!file_exists(__TORQUE_ACCOUNTING__ . $torque_date)) {
			exit;
		}
		$file_handle = @fopen(__TORQUE_ACCOUNTING__ . "/" . $torque_date,"r") or
			die($log->send_log("Error: Torque Accounting file not found in " . TORQUE_ACCOUNTING));
		$number_new_jobs = 0;
		$job_log_xml = torque::get_job_log_xml($torque_date);
		if (!$job_log_xml) {
			$log->send_log("Malformed " . functions::get_torque_job_dir() . $torque_date,log::ERROR);
			print_r(libxml_get_errors());
			exit(1);
		}
		while (($data = fgets($file_handle)) !== FALSE) {
			$result = torque::add_accounting($db,$ldap,$data,$job_log_xml);
			if ($result['RESULT']) {
				$number_new_jobs++;
			}
			if (isset($result['MESSAGE'])) {
				$log->send_log($result['MESSAGE']);
			}
		}

		$msg = $number_new_jobs . " cluster jobs added to accounting database ";
		$msg .= "from file " . __TORQUE_ACCOUNTING__ .  $torque_date;
		break;

	case "SLURM":	
		$job_list = slurm::get_accounting($start_time,$end_time);
		$number_new_jobs = 0;
		if (count($job_list)) {
			foreach ($job_list as $job) {
				$result = slurm::add_accounting($db,$ldap,$job);
				if ($result['RESULT']) {
					$number_new_jobs++;
				}
				if (isset($result['MESSAGE'])) {
					$log->send_log($result['MESSAGE']);
				}

			}
		}
		$msg = $number_new_jobs . " cluster jobs added to accounting database";	
		break;
}
	

	$log->send_log($msg);

?>
