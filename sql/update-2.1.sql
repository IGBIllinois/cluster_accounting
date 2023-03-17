#Fix bug in not disabling CFOPs when a project is disabled
UPDATE cfops LEFT JOIN projects ON projects.project_id=cfops.cfop_project_id 
SET cfops.cfop_active=0 
WHERE cfops.cfop_active=1 AND projects.project_enabled=0
