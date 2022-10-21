CREATE TABLE job_bill (
	job_bill_id INT NOT NOT AUTOINCREMENT,
	job_bill_project_id INT REFERENCES projects(project_id),
	job_bill_cfop_id INT REFERENCES cfops(cfop_id),
	job_bill_date TIMESTAMP,
	job_bill_num_jobs INT,
	job_bill_total_cost DECIMAL(30,7),
	job_bill_billed_cost DECIMAL(30,7),
	PRIMARY KEY (job_bill_id)





);
