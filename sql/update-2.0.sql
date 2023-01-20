CREATE TABLE job_bill (
	job_bill_id INT NOT NULL AUTO_INCREMENT,
	job_bill_user_id INT REFERENCES users(user_id),
	job_bill_project_id INT REFERENCES projects(project_id),
	job_bill_cfop_id INT REFERENCES cfops(cfop_id),
	job_bill_queue_id INT REFERENCES queues(queue_id),
	job_bill_queue_cost_id INT REFERENCES queue_cost(queue_cost_id),
	job_bill_date TIMESTAMP,
	job_bill_num_jobs INT,
	job_bill_total_cost DECIMAL(30,7),
	job_bill_billed_cost DECIMAL(30,7),
	PRIMARY KEY (job_bill_id)
);

DELETE FROM data_cost WHERE data_cost_type<>'standard';
ALTER TABLE data_cost DROP COLUMN data_cost_type;

ALTER TABLE data_cost MODIFY COLUMN data_cost_value DECIMAL(30,2);

ALTER TABLE cfops ADD cfop_billtype ENUM('no_bill','cfop','custom') AFTER cfop_project_id;
ALTER TABLE cfops ADD cfop_custom_description VARCHAR(255) AFTER cfop_activity;
UPDATE cfops SET cfop_billtype='no_bill' WHERE cfop_bill=0;
UPDATE cfops SET cfop_billtype='cfop' WHERE cfop_bill=1;
ALTER TABLE cfops DROP COLUMN cfop_bill;

