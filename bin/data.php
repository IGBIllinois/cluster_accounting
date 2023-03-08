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

//Command parameters
$output_command = "data.php Inserts data usage into database\n";
$output_command .= "Usage: php data.php \n";
$output_command .= "	--dry-run	Do dry run, do not add to database\n";
$output_command .= "    -h, --help              Display help menu\n";

//Parameters
$shortopts = "h";

$longopts = array(
	"dry-run",
        "help"
);

//Following code is to test if the script is being run from the command line or the apache server.
$sapi_type = php_sapi_name();
if ($sapi_type != 'cli') {
	exit("Error: This script can only be run from the command line.");
}

$options = getopt($shortopts,$longopts);

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

$log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_logfile());
	
<<<<<<< HEAD
	$start_time = microtime(true);
	functions::log("Data Usage: Start");	
	$directories = data_functions::get_all_directories($db);
	foreach ($directories as $directory) {
			$data_dir = new data_dir($db,$directory['data_dir_id']);

			$size = 0;
			//if /home/groups/hpcbio
			if ($data_dir->get_data_dir_id() == 75) {
				$size = $data_dir->get_dir_size_du();
			}
			else {
				$size = $data_dir->get_dir_size();
			}
			if (!isset($options['dry-run'])) {
				$result = $data_dir->add_usage($size);
			}
			else {
				$result['RESULT'] = true;
			}	
			if (($result['RESULT']) && (!isset($options['dry-run']))) {
				$message = "Data Usage: Directory: " . $data_dir->get_directory() . " Size: " . data_functions::bytes_to_gigabytes($size) . "GB sucessfully added";
=======
$start_time = microtime(true);
$log->send_log("Data Usage: Start");	
$directories = data_functions::get_all_directories($db);
foreach ($directories as $directory) {
	$data_dir = new data_dir($db,$directory['data_dir_id']);

	$level = \IGBIllinois\log::NOTICE;
	try {
		$data_usage = new \IGBIllinois\data_usage($data_dir->get_directory());
		$size = $data_usage->get_dir_size();

		if (!isset($options['dry-run'])) {
			$result = $data_dir->add_usage($size);
		}
		else {
			$result['RESULT'] = true;
>>>>>>> devel
				
		}	
		if (($result['RESULT']) && (!isset($options['dry-run']))) {
			$message = "Data Usage: Directory: " . $data_dir->get_directory() . " Size: " . data_functions::bytes_to_gigabytes($size) . "GB sucessfully added";
			
		}
		elseif (isset($options['dry-run'])) {
			$message = "DRY-RUN: Data Usage: Directory: " . $data_dir->get_directory() . " Size: " . data_functions::bytes_to_gigabytes($size) . "GB sucessfully added.";
		}
		else {
			$message = "Data Usage: Directory: " . $data_dir->get_directory() . " failed adding to database";
			$level = \IGBIllinois\log::ERROR;
		}
	}
        catch (Exception $e) {
                $message = $e->getMessage();
		$level = \IGBIllinois\log::ERROR;

        }
	$log->send_log($message,$level);
}

$end_time = microtime(true);
$elapsed_time = round($end_time - $start_time,2);
$log->send_log("Data Usage: Finished. Elapsed Time: " . $elapsed_time . " seconds");

?>
