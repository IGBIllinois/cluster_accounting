<?php 
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

include_once '../conf/settings.inc.php';

//Following code is to test if the script is being run from the command line or the apache server.
$sapi_type = php_sapi_name();
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.";
}
else {
	$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

	$filename = "../sql/update-1.2.0-1.3.0.sql";
	if (file_exists($filename)) {
		$update_sql_file = file_get_contents($filename);
		$db->query($update_sql_file);
	}
	$admin_sql = "SELECT users.* FROM users ";
	$admin_sql .= "WHERE users.user_group_id='2'";
	$admin_result = $db->query($admin_sql);

	foreach ($admin_result as $user) {
		$sql = "UPDATE users SET user_admin='1' WHERE user_id='" . $user['user_id'] . "' ";
		$sql .= "LIMIT 1";
		$result = $db->non_select_query($sql);
		print "Update user " . $user['user_name'] . "\n";

	}


	$remove_group_id_sql = "ALTER TABLE users drop column user_group_id";
	print "Remove user_group_id result: " . $db->transaction($remove_group_id_sql) . "\n";




	
	$jobs_sql = "SELECT job_id,job_number FROM jobs WHERE job_number LIKE '%-%'";
	$jobs_result = $db->query($jobs_sql);

	foreach ($jobs_result as $job) {
		$job_number = $job['job_number'];
		$hyphen_pos = strrpos($job_number,"-");

		$job_array = substr($job_number, $hyphen_pos+1);

		$new_job_number = substr($job_number,0,$hyphen_pos);

		$sql = "UPDATE jobs SET job_number_array='" . $job_array . "',job_number='" . $new_job_number . "' WHERE job_id='" . $job['job_id'] . "' LIMIT 1";
		$db->query($sql);

	}
	
	$alter_jobs_sql2 = "ALTER table jobs MODIFY job_number INT";
	print "Alter job_number result: " . $db->transaction($alter_jobs_sql2) . "\n";

        print "Updating Supervisors\n";
        $supervisors_sql = "SELECT user_id FROM users WHERE user_id=user_supervisor";
        $result = $db->query($supervisors_sql);
        foreach ($result as $supervisor) {
                $supervisor_update_sql = "UPDATE users SET user_supervisor='0' WHERE user_id='" . $supervisor['user_id'] . "' LIMIT 1";
                $db->query($supervisor_update_sql);
        }
	print "Done Updating Supervisors\n";






	print "CREATE job_info VIEW\n";
	$job_info_sql = "CREATE VIEW job_info AS
SELECT jobs.job_id as id, IF(ISNULL(jobs.job_number_array),jobs.job_number, CONCAT(jobs.job_number,'[',jobs.job_number_array,']')) as job_number_full, jobs.job_number as job_number, jobs.job_number_array as job_number_array, jobs.job_name as job_name, jobs.job_slots as slots, jobs.job_submission_time as submission_time,jobs.job_start_time as start_time, jobs.job_end_time as end_time, TIME_TO_SEC(TIMEDIFF(jobs.job_end_time,jobs.job_start_time)) as elapsed_time, jobs.job_ru_wallclock as wallclock_time, jobs.job_cpu_time as cpu_time, jobs.job_total_cost as total_cost, jobs.job_billed_cost as billed_cost, jobs.job_reserved_mem as reserved_mem, jobs.job_used_mem as used_mem, jobs.job_maxvmem as maxvmem, job_queue_id as queue_id, jobs.job_exit_status as exit_status, jobs.job_exec_hosts as exec_hosts, jobs.job_qsub_script as qsub_script, jobs.job_project as submitted_project, TIME_TO_SEC(TIMEDIFF(jobs.job_start_time,jobs.job_submission_time)) as queued_time, users.user_id as user_id, users.user_name as username, projects.project_name as project_name, projects.project_id as project_id, cfops.cfop_value as cfop, cfops.cfop_activity as activity_code, queues.queue_name as queue_name FROM jobs LEFT JOIN users ON jobs.job_user_id=users.user_id LEFT JOIN projects ON projects.project_id=jobs.job_project_id LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id LEFT JOIN cfops ON cfops.cfop_id=jobs.job_cfop_id LEFT JOIN queue_cost ON queue_cost.queue_cost_id=jobs.job_queue_cost_id";
	print "CREATE VIEW job_info result: " . $db->transaction($job_info_sql) . "\n";


	print "Update CFOP active\n";
	$alter_cfop_3 = "SELECT DISTINCT cfop_project_id as project_id FROM cfops";
	$result = $db->query($alter_cfop_3);

	foreach ($result as $cfop) {
		$sql = "UPDATE cfops SET cfop_active='1' WHERE cfop_project_id='" . $cfop['project_id'] . "' ORDER BY cfop_time_created DESC LIMIT 1";
		$db->non_select_query($sql);

	}
	
	print "Done\n";
}
	

?>
