DROP VIEW IF EXISTS data_info;
CREATE VIEW data_info AS
SELECT data_usage.data_usage_id AS id,data_usage.data_usage_bytes AS bytes,data_usage.data_usage_time AS time,projects.project_name AS project_name,data_dir.data_dir_path AS path,cfops.cfop_value AS `cfop`,`cfops`.`cfop_activity` AS cfop_activity from data_usage left join projects on projects.project_id = data_usage.data_usage_project_id left join data_dir on data_dir.data_dir_id = data_usage.data_usage_data_dir_id left join cfops on cfops.cfop_id = data_usage.data_usage_cfop_id;
ALTER TABLE data_dir DROP COLUMN data_dir_data_cost_id;
ALTER TABLE data_usage DROP COLUMN data_usage_data_cost_id;

