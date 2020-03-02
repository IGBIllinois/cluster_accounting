ALTER TABLE cfops ADD cfop_billtype ENUM('no_bill','cfop','custom'), ADD cfop_custom_description VARCHAR(255);
UPDATE cfops SET cfop_billtype='no_bill' WHERE cfop_bill=0;
UPDATE cfops SET cfop_billtype='cfop' WHERE cfop_bill=1;
ALTER TABLE cfops DROP COLUMN cfop_bill;

CREATE INDEX job_user_id ON jobs(job_user_id);
CREATE INDEX  job_end_time ON jobs(job_end_time);


