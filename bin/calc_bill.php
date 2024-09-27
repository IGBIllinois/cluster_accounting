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
$output_command = "calc_bill.php Calculates Data and Job Monthly Billing\n";
$output_command .= "Defaults to previous month\n";
$output_command .= "Usage: php data.php \n";
$output_command .= "	--year			Year (YYYY)\n";
$output_command .= "	--month			Month (MM)\n";
$output_command .= "	--data			Calculate Data Only\n";
$output_command .= "	--jobs			Calculate Jobs Only\n";
$output_command .= "	--dry-run		Do Dry Run Only\n";
$output_command .= "	-h, --help              Display help menu\n";

//Parameters
$shortopts = "";
$shortopts .= "h";

$longopts = array(
        "help",
	"year::",
	"month::",
	"data",
	"jobs",
	"dry-run"
);

//Following code is to test if the script is being run from the command line or the apache server.
if (php_sapi_name() != 'cli') {
	exit("Error: This script can only be run from the command line.");
}
	
$year = date("Y",strtotime("-1 month"));
$month = date("m",strtotime("-1 month"));
$calc_data = true;
$calc_jobs = true;
$dry_run = false;

$options = getopt($shortopts,$longopts);
if (isset($options['h']) || isset($options['help'])) {
	echo $output_command;
	exit;
}

if ((isset($options['d']) || isset($options['data'])) && (isset($options['j']) || isset($options['jobs']))) {
	echo "Please specify -d/--data or -j/--jobs\n";
	exit(1);
}
elseif (isset($options['d']) || isset($options['data'])) {
	$calc_data = true;
	$calc_jobs = false;
}
elseif (isset($options['j']) || isset($options['jobs'])) {
	$calc_data = false;
	$calc_jobs = true;
}

if (isset($options['dry-run'])) {
	$dry_run = true;
}
if (isset($options['year']) && !isset($options['month'])) {
	echo "Must specify a year and month\n";
	exit(1);
}
elseif (!isset($options['year']) && isset($options['month'])) {
	echo "Must specify a year and month\n";
	exit(1);
}
elseif ((isset($options['year'])) && isset($options['month'])) {
	$year = $options['year'];
	$month = $options['month'];
	
}

$db = new \IGBIllinois\db(settings::get_mysql_host(),
                        settings::get_mysql_database(),
                        settings::get_mysql_user(),
                        settings::get_mysql_password(),
                        settings::get_mysql_ssl(),
                        settings::get_mysql_port()
                        );

$log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_logfile());

if ($calc_data) {
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
		if (!$dry_run) {
			$result = $data_dir->add_data_bill($month,$year,$average);
		}
		$log->send_log($result['MESSAGE']);
	}
}

if ($calc_jobs) {
	$jobs_bill = job_functions::get_all_jobs_by_month($db,$month,$year);
	foreach ($jobs_bill as $job_info) {
		if (!$dry_run) {
			$result = job_bill::add_job_bill($db,$job_info);
			$log->send_log($result['MESSAGE']);
		}
	}
}
?>
