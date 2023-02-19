CREATE TABLE job_bill (
	job_bill_id INT NOT NULL AUTO_INCREMENT,
	job_bill_user_id INT REFERENCES users(user_id),
	job_bill_project_id INT REFERENCES projects(project_id),
	job_bill_cfop_id INT REFERENCES cfops(cfop_id),
	job_bill_queue_id INT REFERENCES queues(queue_id),
	job_bill_queue_cost_id INT REFERENCES queue_cost(queue_cost_id),
	job_bill_date TIMESTAMP,
	job_bill_num_jobs INT,
	job_bill_total_cost DECIMAL(30,2),
	job_bill_billed_cost DECIMAL(30,2),
	PRIMARY KEY (job_bill_id)
);

DELETE FROM data_cost WHERE data_cost_type<>'standard';
ALTER TABLE data_cost DROP COLUMN data_cost_type;

ALTER TABLE data_cost MODIFY COLUMN data_cost_value DECIMAL(30,2);

ALTER TABLE cfops ADD cfop_billtype ENUM('no_bill','cfop','custom') AFTER cfop_project_id;
ALTER TABLE cfops ADD cfop_custom_description VARCHAR(255) AFTER cfop_activity;
UPDATE cfops SET cfop_billtype='no_bill' WHERE cfop_bill=0;
UPDATE cfops SET cfop_billtype='cfop' WHERE cfop_bill=1;
UPDATE cfops SET cfop_billtype='custom',cfop_custom_description='Credit Card',cfop_value='',cfop_activity='' WHERE cfop_value='5-555555-555555-555555';

ALTER TABLE cfops DROP COLUMN cfop_bill;
ALTER TABLE data_bill MODIFY COLUMN data_bill_total_cost DECIMAL(30,2);
ALTER TABLE data_bill MODIFY COLUMN data_bill_billed_cost DECIMAL(30,2);

ALTER TABLE data_usage DROP COLUMN data_usage_files;

ALTER TABLE users ADD user_firstname VARCHAR(100) AFTER user_full_name;
ALTER TABLE users ADD user_lastname VARCHAR(100) AFTER user_firstname;
UPDATE users SET user_firstname=(SUBSTR(user_full_name,1,(LOCATE(' ',user_full_name)))), user_lastname=(SUBSTR(user_full_name,(LOCATE(' ',user_full_name))));
ALTER TABLE users DROP COLUMN users.user_full_name;

