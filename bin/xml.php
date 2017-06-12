<?php

chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

include_once '../conf/settings.inc.php';


$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.\n";
}
else {
	$torque_date = date('Ymd');
	if (isset($argv[1])) {
		$torque_date = $argv[1];
	}
	if (!file_exists(__TORQUE_ACCOUNTING__ . $torque_date)) {
		exit;
	}
	$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
	$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	$file_handle = @fopen(__TORQUE_ACCOUNTING__ . "/" . $torque_date,"r") or
		die("Error: Torque Accounting file not found in " . __TORQUE_ACCOUNTING__ . "\n");
	$number_new_jobs = 0;
	$job_log_xml = torque::get_job_log_xml($torque_date);
	if (!$job_log_xml) {
		print "Malformed " . functions::get_torque_job_dir() . $torque_date . "\n";
		print_r(libxml_get_errors());
		exit;
	}
	while (($data = fgets($file_handle)) !== FALSE) {

		$result = torque::add_torque_script($db,$ldap,$data,$job_log_xml);
		if ($result['RESULT']) {
			$number_new_jobs++;
		}
		elseif (isset($result['MESSAGE'])) {
			print $result['MESSAGE'] . "\n";
		}
	}
	print "\n" . date("Y-m-d") . ": " . $number_new_jobs . " cluster scripts added to accounting database " .
			"from file " . __TORQUE_ACCOUNTING__ .  $torque_date . "\n";



}

?>
