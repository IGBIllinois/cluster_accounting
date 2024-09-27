DROP VIEW job_info;
CREATE VIEW job_info AS
SELECT jobs.job_id as id, IF(ISNULL(jobs.job_number_array),jobs.job_number, CONCAT(jobs.job_number,'[',jobs.job_number_array,']')) as job_number_full, jobs.job_number as job_number, jobs.job_number_array as job_number_array, jobs.job_name as job_name, jobs.job_slots as slots, jobs.job_submission_time as submission_time,jobs.job_start_time as start_time, jobs.job_end_time as end_time, TIME_TO_SEC(TIMEDIFF(jobs.job_end_time,jobs.job_start_time)) as elapsed_time, jobs.job_ru_wallclock as wallclock_time, jobs.job_cpu_time as cpu_time, jobs.job_total_cost as total_cost, jobs.job_billed_cost as billed_cost, jobs.job_reserved_mem as reserved_mem, jobs.job_used_mem as used_mem, jobs.job_maxvmem as maxvmem, job_queue_id as queue_id, jobs.job_exit_status as exit_status, jobs.job_exec_hosts as exec_hosts, jobs.job_qsub_script as qsub_script, jobs.job_project as submitted_project, TIME_TO_SEC(TIMEDIFF(jobs.job_start_time,jobs.job_submission_time)) as queued_time, users.user_id as user_id, users.user_name as username, projects.project_name as project_name, projects.project_id as project_id, queues.queue_name as queue_name,jobs.job_gpu as gpu,jobs.job_state as state FROM jobs LEFT JOIN users ON jobs.job_user_id=users.user_id LEFT JOIN projects ON projects.project_id=jobs.job_project_id LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id LEFT JOIN queue_cost ON queue_cost.queue_cost_id=jobs.job_queue_cost_id;

ALTER TABLE jobs DROP COLUMN job_cfop_id;

