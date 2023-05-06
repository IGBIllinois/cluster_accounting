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
$output_command = "running_jobs.php - Adds Running Jobs to Memory Database Table.\n";
$output_command .= "Usage: php accounting.php \n";
$output_command .= "	-h, --help 		Display help menu\n";

//Parameters
$shortopts = "h";

$longopts = array(
	"help"
);

//If not run from command line
if (php_sapi_name() != 'cli') {
	exit("Error: This script can only be run from the command line.\n");
}
	
$options = getopt($shortopts,$longopts);

//verify options are specified correctly
if (isset($options['h']) || isset($options['help'])) {
	echo $output_command;
	exit;
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

running_job::truncate_table($db);

switch(settings::get_job_scheduler()) {

	case "SLURM":	
		$job_list = slurm::get_running_jobs();
		$number_new_jobs = 0;
		if (count($job_list)) {
			foreach ($job_list as $job) {
				$result = slurm::add_running_job($db,$ldap,$job);
				if ($result['RESULT']) {
					$number_new_jobs++;
				}
				if (isset($result['MESSAGE'])) {
					$log->send_log($result['MESSAGE']);
				}

			}
		}
		$msg = $number_new_jobs . " running cluster jobs added to accounting database";	
		break;
}
	

	$log->send_log($msg);

?>
