ALTER TABLE `jobs` ADD INDEX (job_number);
ALTER TABLE users ADD user_admin BOOLEAN DEFAULT 0 AFTER user_id;
DROP TABLE groups;
ALTER TABLE queues ADD queue_public BOOLEAN DEFAULT FALSE AFTER queue_enabled;
UPDATE queues SET queue_public='1';
ALTER TABLE jobs ADD job_number_array INT DEFAULT NULL AFTER job_number;
ALTER TABLE data_dir ADD data_dir_default BOOLEAN DEFAULT 0;
UPDATE data_dir SET data_dir_default='1' WHERE data_dir_path LIKE '%a-m%' OR data_dir_path LIKE '%n-z%';
ALTER TABLE cfops ADD cfop_restricted BOOLEAN DEFAULT 0 AFTER cfop_activity;
ALTER TABLE cfops ADD cfop_active BOOLEAN DEFAULT 0 AFTER cfop_restricted;
ALTER table jobs ADD job_exec_hosts TEXT AFTER job_exit_status;
ALTER table jobs ADD job_qsub_script TEXT AFTER job_exec_hosts;

