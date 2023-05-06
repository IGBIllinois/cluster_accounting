ALTER TABLE job_bill MODIFY COLUMN job_bill_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

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
        job_elapsed_time BIGINT UNSIGNED,
        job_ru_wallclock BIGINT UNSIGNED,
        job_cpu_time BIGINT UNSIGNED,
        job_reserved_mem BIGINT UNSIGNED,
        job_exec_hosts VARCHAR(255),
        job_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        job_gpu INT DEFAULT 0,
        job_state VARCHAR(20) DEFAULT "",
        PRIMARY KEY(job_id)
);


