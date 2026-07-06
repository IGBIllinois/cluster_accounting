ALTER TABLE queues ADD COLUMN queue_skucode VARCHAR(100) DEFAULT '' AFTER queue_description;

UPDATE projects 
LEFT JOIN users ON users.user_id=projects.project_owner
SET projects.project_enabled=0 
WHERE projects.project_enabled=1 AND projects.project_default=1 AND users.user_enabled=0

UPDATE cfops
LEFT JOIN projects ON projects.project_id=cfops.cfop_project_id
SET cfops.cfop_active=0
WHERE cfops.cfop_active=1 AND projects.project_enabled=0

UPDATE data_dir
LEFT JOIN projects ON projects.project_id=data_dir.data_dir_project_id
SET data_dir.data_dir_enabled=0
WHERE data_dir.data_dir_enabled=1 AND projects.project_enabled=0

