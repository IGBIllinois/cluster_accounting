ALTER TABLE data_cost RENAME COLUMN data_cost_dir to data_cost_type;
ALTER TABLE data_dir ADD data_dir_data_cost_id INT REFERENCES data_cost.data_cost_id;
ALTER TABLE jobs MODIFY job_exit_status VARCHAR(10);
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
