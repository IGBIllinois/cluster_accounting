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
$output_command = "notify_long_running_jobs.php Emails users whose jobs have been running longer than a configured number of days\n";
$output_command .= "Usage: php notify_long_running_jobs.php \n";
$output_command .= "	--queue			Queue name to check (Default: all queues)\n";
$output_command .= "	--days			Number of days a job must be running before notifying (Default: " . settings::get_long_running_job_days() . ")\n";
$output_command .= "	--repeat-count		Number of additional times to repeat the notification while the job is still running (Default: " . settings::get_long_running_job_repeat_count() . ")\n";
$output_command .= "	--repeat-days		Number of days to wait between repeat notifications (Default: " . settings::get_long_running_job_repeat_days() . ")\n";
$output_command .= "	-h, --help		Display help menu\n";

//Parameters
$shortopts = "h";

$longopts = array(
        "help",
	"queue::",
	"days::",
	"repeat-count::",
	"repeat-days::"
);

//Following code is to test if the script is being run from the command line or the apache server.
if (php_sapi_name() != 'cli') {
        exit("Error: This script can only be run from the command line.");
}

$options = getopt($shortopts,$longopts);

if (isset($options['h']) || isset($options['help'])) {
	echo $output_command;
	exit;
}

$queue_name = null;
$days = settings::get_long_running_job_days();
$repeat_count = settings::get_long_running_job_repeat_count();
$repeat_days = settings::get_long_running_job_repeat_days();

if (isset($options['queue'])) {
	$queue_name = $options['queue'];
}
if (isset($options['days'])) {
	$days = (int)$options['days'];
}
if (isset($options['repeat-count'])) {
	$repeat_count = (int)$options['repeat-count'];
}
if (isset($options['repeat-days'])) {
	$repeat_days = (int)$options['repeat-days'];
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

job_functions::cleanup_long_running_job_notifications($db);

$jobs = job_functions::get_long_running_jobs($db,$queue_name,$days,$repeat_days,$repeat_count);

$jobs_by_user = array();
foreach ($jobs as $job) {
	$jobs_by_user[$job['user_id']][] = $job;
}

foreach ($jobs_by_user as $user_id => $user_jobs) {
	$user_object = new user($db,$ldap,$user_id);
	$level = \IGBIllinois\log::NOTICE;
	try {
		$user_object->email_long_running_jobs($user_jobs,$days,settings::get_website_url(),settings::get_admin_email());
		foreach ($user_jobs as $job) {
			job_functions::record_long_running_job_notification($db,$job['job_number_raw'],$job['job_number_array'],
				$job['username'],$job['start_time']);
		}
		$message = "Email Long Running Jobs - User " . $user_object->get_username() . " successfully sent to " . $user_object->get_email();
	}
	catch (\Exception $e) {
		$level = \IGBIllinois\log::ERROR;
		$message = $e->getMessage();
	}
	$log->send_log($message,$level);
}

?>
