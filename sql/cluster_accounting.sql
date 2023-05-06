CREATE TABLE users (
	user_id INT NOT NULL AUTO_INCREMENT,
	user_admin BOOLEAN DEFAULT 0,
	user_name VARCHAR(30),
	user_supervisor INT REFERENCES users(user_id),
	user_firstname VARCHAR(100),
	user_lastname VARCHAR(100),
	user_time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	user_enabled BOOLEAN DEFAULT 1,
	CONSTRAINT username UNIQUE(user_name),
	PRIMARY KEY(user_id)
);

CREATE TABLE projects (
	project_id INT NOT NULL AUTO_INCREMENT,
	project_owner INT REFERENCES users(user_id),
	project_name VARCHAR(50),
	project_ldap_group VARCHAR(50),
	project_description VARCHAR(100),
	project_time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	project_default BOOLEAN DEFAULT 0,
	project_enabled BOOLEAN DEFAULT 1,
	PRIMARY KEY(project_id)
);

CREATE TABLE cfops(
	cfop_id INT NOT NULL AUTO_INCREMENT,
	cfop_project_id INT REFERENCES projects(project_id),
	cfop_billtype ENUM('no_bill','cfop','custom'),
	cfop_value VARCHAR(22),
	cfop_activity VARCHAR(6),
	cfop_custom_description VARCHAR(255),
	cfop_restricted BOOLEAN DEFAULT 0,
	cfop_time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(cfop_id)
);

CREATE TABLE queues (
	queue_id INT NOT NULL AUTO_INCREMENT,
	queue_name VARCHAR(50),
	queue_ldap_group VARCHAR(50),
	queue_description VARCHAR(100),
	queue_time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	queue_enabled BOOLEAN DEFAULT TRUE,
	PRIMARY KEY(queue_id)
);

CREATE TABLE queue_cost(
	queue_cost_id INT NOT NULL AUTO_INCREMENT,
	queue_cost_queue_id INT REFERENCES queues(queue_id),
	queue_cost_mem DECIMAL(30,9),
	queue_cost_cpu DECIMAL(30,9),
	queue_cost_gpu DECIMAL(30,9),
	queue_cost_time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(queue_cost_id)

);
CREATE TABLE jobs (
	job_id INT NOT NULL AUTO_INCREMENT,
	job_user_id INT REFERENCES users(user_id),
	job_project_id INT REFERENCES projects(project_id),	
	job_queue_id INT REFERENCES queue(queue_id),
	job_cfop_id INT REFERENCES cfop(cfop_id),
	job_queue_cost_id INT REFERENCES queue_cost(queue_cost_id),
	job_number VARCHAR(20),
	job_number_array INT DEFAULT 0,
	job_user VARCHAR(25),
	job_project VARCHAR(50),
	job_name VARCHAR(255),
	job_queue_name VARCHAR(30),
	job_slots INT,
	job_total_cost DECIMAL(30,7) DEFAULT 0.0000000,
	job_billed_cost DECIMAL(30,7) DEFAULT 0.0000000,
	job_submission_time DATETIME,
	job_start_time DATETIME,
	job_end_time DATETIME,
	job_ru_wallclock BIGINT UNSIGNED,
	job_cpu_time BIGINT UNSIGNED,
	job_reserved_mem BIGINT UNSIGNED,
	job_used_mem BIGINT UNSIGNED,
	job_exit_status VARCHAR(10),
	job_exec_hosts VARCHAR(255),
	job_qsub_script TEXT,	
	job_maxvmem BIGINT UNSIGNED,
	job_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	job_gpu INT DEFAULT 0,
	job_state VARCHAR(20) DEFAULT "",
	PRIMARY KEY(job_id)
);

CREATE TABLE running_jobs (
	job_id INT NOT NULL AUTO_INCREMENT,
	job_user_id INT REFERENCES users(user_id),
        job_project_id INT REFERENCES projects(project_id),
        job_queue_id INT REFERENCES queue(queue_id),
        job_cfop_id INT REFERENCES cfop(cfop_id),
        job_queue_cost_id INT REFERENCES queue_cost(queue_cost_id),
        job_number VARCHAR(20),
        job_number_array INT DEFAULT 0,
        job_user VARCHAR(25),
        job_project VARCHAR(50),
        job_name VARCHAR(255),
        job_queue_name VARCHAR(30),
        job_slots INT,
        job_estimated_cost DECIMAL(30,7) DEFAULT 0.0000000,
        job_submission_time DATETIME,
        job_start_time DATETIME,
        job_ru_wallclock BIGINT UNSIGNED,
        job_cpu_time BIGINT UNSIGNED,
        job_reserved_mem BIGINT UNSIGNED,
        job_used_mem BIGINT UNSIGNED,
        job_exec_hosts VARCHAR(255),
        job_maxvmem BIGINT UNSIGNED,
        job_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        job_gpu INT DEFAULT 0,
        job_state VARCHAR(20) DEFAULT "",
        PRIMARY KEY(job_id)
) ENGINE = MEMORY;


CREATE TABLE data_cost(
	data_cost_id INT NOT NULL AUTO_INCREMENT,
	data_cost_value DECIMAL(30,2),
	data_cost_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	data_cost_enabled BOOLEAN DEFAULT TRUE,
	PRIMARY KEY (data_cost_id)
);

CREATE TABLE data_dir (
        data_dir_id INT NOT NULL AUTO_INCREMENT,
        data_dir_project_id INT REFERENCES projects(project_id),
        data_dir_path VARCHAR(255),
        data_dir_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_dir_enabled BOOLEAN DEFAULT TRUE,
        data_dir_default BOOLEAN DEFAULT FALSE,
        PRIMARY KEY (data_dir_id)
);

CREATE TABLE data_usage (
	data_usage_id INT NOT NULL AUTO_INCREMENT,
	data_usage_project_id INT REFERENCES projects(project_id),
	data_usage_data_dir_id INT REFERENCES data_dir(data_dir_id),
	data_usage_cfop_id INT REFERENCES cfops(cfop_id),
	data_usage_bytes BIGINT UNSIGNED,
	data_usage_files BIGINT UNSIGNED,
	data_usage_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (data_usage_id)
);

CREATE TABLE data_bill (
        data_bill_id INT NOT NULL AUTO_INCREMENT,
        data_bill_data_dir_id INT REFERENCES data_dir(data_dir_id),
        data_bill_data_cost_id INT REFERENCES data_cost(data_cost_id),
        data_bill_project_id INT REFERENCES projects(project_id),
        data_bill_cfop_id INT REFERENCES cfops(cfop_id),
        data_bill_date TIMESTAMP,
        data_bill_avg_bytes BIGINT(20) DEFAULT 0,
        data_bill_total_cost DECIMAL(30,7),
        data_bill_billed_cost DECIMAL(30,7),
        PRIMARY KEY(data_bill_id)
);

CREATE TABLE job_bill (
        job_bill_id INT NOT NULL AUTO_INCREMENT,
        job_bill_user_id INT REFERENCES users(user_id),
        job_bill_project_id INT REFERENCES projects(project_id),
        job_bill_cfop_id INT REFERENCES cfops(cfop_id),
        job_bill_queue_id INT REFERENCES queues(queue_id),
        job_bill_queue_cost_id INT REFERENCES queue_cost(queue_cost_id),
        job_bill_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        job_bill_num_jobs INT,
        job_bill_total_cost DECIMAL(30,7),
        job_bill_billed_cost DECIMAL(30,7),
        PRIMARY KEY (job_bill_id)
);

CREATE VIEW job_info AS
SELECT jobs.job_id as id, IF(ISNULL(jobs.job_number_array),jobs.job_number, CONCAT(jobs.job_number,'[',jobs.job_number_array,']')) as job_number_full, jobs.job_number as job_number, jobs.job_number_array as job_number_array, jobs.job_name as job_name, jobs.job_slots as slots, jobs.job_submission_time as submission_time,jobs.job_start_time as start_time, jobs.job_end_time as end_time, TIME_TO_SEC(TIMEDIFF(jobs.job_end_time,jobs.job_start_time)) as elapsed_time, jobs.job_ru_wallclock as wallclock_time, jobs.job_cpu_time as cpu_time, jobs.job_total_cost as total_cost, jobs.job_billed_cost as billed_cost, jobs.job_reserved_mem as reserved_mem, jobs.job_used_mem as used_mem, jobs.job_maxvmem as maxvmem, job_queue_id as queue_id, jobs.job_exit_status as exit_status, jobs.job_exec_hosts as exec_hosts, jobs.job_qsub_script as qsub_script, jobs.job_project as submitted_project, TIME_TO_SEC(TIMEDIFF(jobs.job_start_time,jobs.job_submission_time)) as queued_time, users.user_id as user_id, users.user_name as username, projects.project_name as project_name, projects.project_id as project_id, cfops.cfop_value as cfop, cfops.cfop_activity as activity_code, queues.queue_name as queue_name FROM jobs LEFT JOIN users ON jobs.job_user_id=users.user_id LEFT JOIN projects ON projects.project_id=jobs.job_project_id LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id LEFT JOIN cfops ON cfops.cfop_id=jobs.job_cfop_id LEFT JOIN queue_cost ON queue_cost.queue_cost_id=jobs.job_queue_cost_id;

CREATE VIEW data_info AS
SELECT data_usage.data_usage_id AS id,data_usage.data_usage_bytes AS bytes,data_usage.data_usage_time AS time,projects.project_name AS project_name,data_dir.data_dir_path AS path,cfops.cfop_value AS `cfop`,`cfops`.`cfop_activity` AS cfop_activity from data_usage left join projects on projects.project_id = data_usage.data_usage_project_id left join data_dir on data_dir.data_dir_id = data_usage.data_usage_data_dir_id left join cfops on cfops.cfop_id = data_usage.data_usage_cfop_id;

