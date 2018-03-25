ALTER TABLE cfops ADD cfop_custombill BOOLEAN DEFAULT 0 AFTER cfop_restricted;
ALTER TABLE cfops ADD cfop_notes TEXT DEFAULT '' AFTER cfop_custombill;

