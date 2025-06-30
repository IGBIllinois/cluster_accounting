ALTER TABLE data_usage DROP COLUMN data_usage_cfop_id;
ALTER TABLE data_usage DROP COLUMN data_usage_project_id;
DROP VIEW data_info;
CREATE VIEW data_info AS
SELECT data_usage.data_usage_id AS id,
data_usage.data_usage_bytes AS bytes,
data_usage.data_usage_time AS time,
projects.project_name AS project_name,
data_dir.data_dir_path AS path
FROM data_usage
LEFT JOIN data_dir on data_dir.data_dir_id = data_usage.data_usage_data_dir_id
LEFT JOIN projects on projects.project_id = data_dir.data_dir_project_id

