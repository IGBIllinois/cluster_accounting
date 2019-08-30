ALTER TABLE cfops ADD cfop_billtype ENUM('no_bill','cfop','custom'), ADD cfop_custom_description VARCHAR(255);
UPDATE cfops SET cfop_billtype='no_bill' WHERE cfop_bill=0;
UPDATE cfops SET cfop_billtype='cfop' WHERE cfop_bill=1;
ALTER TABLE cfops DROP COLUMN cfop_bill;


