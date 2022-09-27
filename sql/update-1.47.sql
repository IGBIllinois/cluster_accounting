CREATE VIEW data_info AS
SELECT data_usage.data_usage_id AS id,data_usage.data_usage_bytes AS bytes,data_usage.data_usage_time AS time,projects.project_name AS project_name,data_dir.data_dir_path AS path,data_cost.data_cost_value AS rate,cfops.cfop_value AS `cfop`,`cfops`.`cfop_activity` AS cfop_activity from ((((data_usage left join projects on((projects.project_id = data_usage.data_usage_project_id))) left join data_dir on((data_dir.data_dir_id = data_usage.data_usage_data_dir_id))) left join data_cost on((data_cost.data_cost_id = data_usage.data_usage_data_cost_id))) left join cfops on((cfops.cfop_id = data_usage.data_usage_cfop_id)));

ALTER TABLE data_bill CHANGE data_bill_date data_bill_date TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
