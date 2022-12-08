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
$output_command = "calc_bill.php Calculates Data and Job Billing\n";
$output_command .= "Defaults to previous month\n";
$output_command .= "Usage: php data.php \n";
$output_command .= "	-y, --year		Year (YYYY)\n";
$output_command .= "	-m, --month		Month (MM)\n";
$output_command .= "	-d, --data		Calculate Data Only\n";
$output_command .= "	-j, --jobs		Calculate Jobs Only\n";
$output_command .= "	-h, --help              Display help menu\n";

//Parameters
$shortopts = "";
$shortopts .= "h";
$shortopts .= "y:";
$shortopts .= "m:";
$shortopts .= "d";
$shortopts .= "j";

$longopts = array(
        "help",
	"year::",
	"month::",
	"data",
	"jobs",
);

//Following code is to test if the script is being run from the command line or the apache server.
if (php_sapi_name() != 'cli') {
	exit("Error: This script can only be run from the command line.");
}
	
$year = date("Y",strtotime("-1 month"));
$month = date("m",strtotime("-1 month"));
$calc_data = true;
$calc_jobs = true;

$options = getopt($shortopts,$longopts);
if (isset($options['h']) || isset($options['help'])) {
	echo $output_command;
	exit;
}
if ((isset($options['y']) || isset($options['year'])) 
	&& (isset($options['m']) || isset($options['month']))) {
	if (isset($options['y'])) {
		$year = $options['y'];
	}
	elseif (isset($options['year'])) {
		$year = $options['year'];
	}
	if (isset($options['m'])) {
		$month = $options['m'];
	}
	elseif (isset($options['month'])) {
		$month = $options['month'];
	}
}

$db = new \IGBIllinois\db(settings::get_mysql_host(),
                        settings::get_mysql_database(),
                        settings::get_mysql_user(),
                        settings::get_mysql_password(),
                        settings::get_mysql_ssl(),
                        settings::get_mysql_port()
                        );

$log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_logfile());
/*
$directories = data_functions::get_all_directories($db);
foreach ($directories as $directory) {
	$data_dir = new data_dir($db,$directory['data_dir_id']);
	$data_usage = $data_dir->get_usage($month,$year);
	$count = count($data_usage);
	$sum = 0;
	foreach ($data_usage as $usage) {
		$sum += $usage['data_usage_bytes'];
	}
	$average = round($sum / $count);
	$result = $data_dir->add_data_bill($month,$year,$average);
	$log->send_log($result['MESSAGE']);
}
*/

$enabled = 0;
$jobs_bill = job_functions::get_jobs_bill_new($db,$month,$year);

$bill_date = $year . "-" . $month . "-01 00:00:00";
foreach ($jobs_bill as $job_info) {
	$job_info['date'] = $bill_date;
	job_bill::add_job_usage($db,$job_info);
}

?>
