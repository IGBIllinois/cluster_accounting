<?php

class data_functions {

	public const CONVERT_TERABYTES = 1099511627776;
        public const CONVERT_GIGABYTES = 1073741824;

	public static function get_directories($db,$default = 1,$start,$count) {
		$sql = "SELECT data_dir.*, projects.project_name, projects.project_id ";
		$sql .= "FROM data_dir ";
		$sql .= "LEFT JOIN projects ON projects.project_id=data_dir.data_dir_project_id ";
		$sql .= "WHERE data_dir_enabled='1' ";
		$sql .= "AND data_dir_default=:default ";	
		$sql .= "ORDER BY data_dir.data_dir_path ASC ";
		if ($count != 0) {
			$sql .= "LIMIT " . $start . "," . $count;
		}
		$parameters = array(
			':default'=>$default
		);
		$result = $db->query($sql,$parameters);
	
		for ($i=0;$i<count($result);$i++) {
			if (is_dir($result[$i]['data_dir_path'])) {
				$result[$i]['dir_exists'] = true;
			}
			else { 
				$result[$i]['dir_exists'] = false;
			}
		}
		return $result;
	}

	public static function get_all_directories($db) {
		$sql = "SELECT data_dir.*, projects.project_name, projects.project_id ";
                $sql .= "FROM data_dir ";
                $sql .= "LEFT JOIN projects ON projects.project_id=data_dir.data_dir_project_id ";
                $sql .= "WHERE data_dir_enabled='1' ";
                $sql .= "ORDER BY data_dir.data_dir_path ASC ";

                $result = $db->query($sql);

                for ($i=0;$i<count($result);$i++) {
                        if (is_dir($result[$i]['data_dir_path'])) {
                                $result[$i]['dir_exists'] = true;
                        }
                        else {
                                $result[$i]['dir_exists'] = false;
                        }
                }
                return $result;


	}
	public static function get_num_directories($db,$default = 1) {
		$sql = "SELECT count(1) as count FROM data_dir ";
		$sql .= "WHERE data_dir_enabled='1' ";
		$sql .= "AND data_dir_default=:default";
		$parameters = array(
			':default'=>$default
		);
		$result = $db->query($sql,$parameters);
		return $result[0]['count'];
	}

	public static function get_current_data_cost($db) {
		$sql = "SELECT data_cost.data_cost_id as id, ";
		$sql .= "ROUND(data_cost_value,2) as cost, ";
		$sql .= "data_cost_time as time ";
		$sql .= "FROM data_cost ";
		$sql .= "WHERE data_cost_enabled='1' ";
		$sql .= "LIMIT 1";
		$result = $db->query($sql);
		if (count($result)) {
			return new data_cost($db,$result[0]['id']);
		}
		return false;
	}

	public static function get_data_bill($db,$month,$year) {
		$sql = "SELECT data_dir.data_dir_path as 'Directory', ";
	        $sql .= "ROUND(data_bill.data_bill_avg_bytes / :terabytes,3) as 'Terabytes', ";
        	$sql .= "ROUND(data_cost.data_cost_value,2) as 'Rate ($/Terabyte)', ";
        	$sql .= "ROUND(data_bill.data_bill_total_cost,2) as 'Total Cost', ";
	        $sql .= "ROUND(data_bill.data_bill_billed_cost,2) as 'Billed Cost', ";
        	$sql .= "projects.project_name as 'Project', ";
	        $sql .= "cfops.cfop_value as 'CFOP', cfops.cfop_activity as 'Activity Code', ";
		$sql .= "cfops.cfop_billtype as 'Bill Type' ";
        	$sql .= "FROM data_bill ";
	        $sql .= "LEFT JOIN cfops ON cfops.cfop_id=data_bill.data_bill_cfop_id ";
        	$sql .= "LEFT JOIN projects ON projects.project_id=data_bill.data_bill_project_id ";
	        $sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_bill.data_bill_data_dir_id ";
        	$sql .= "LEFT JOIN data_cost ON data_cost_id=data_bill_data_cost_id ";
	        $sql .= "WHERE YEAR(data_bill.data_bill_date)=:year ";
        	$sql .= "AND MONTH(data_bill.data_bill_date)=:month ";
		$sql .= "ORDER BY Directory ASC";
		$parameters = array(
			':month'=>$month,
			':year'=>$year,
			':terabytes'=>data_functions::CONVERT_TERABYTES
		);
        	return $db->query($sql,$parameters);
	}

        public static function get_data_boa_bill($db,$month,$year,$minimal_bill = 0.01) {
                $sql = "SELECT '' as 'DATE', ";
		$sql .= "projects.project_name as 'NAME', ";
		$sql .= "cfops.cfop_value as 'CFOP', ";
		$sql .= "cfops.cfop_activity as 'ACTIVITY CODE', ";	
                $sql .= "ROUND(data_bill.data_bill_billed_cost,2)  as 'COST', ";
		$sql .= "CONCAT('Biocluster Data - ',data_dir.data_dir_path) as 'DESCRIPTION' ";
                $sql .= "FROM data_bill ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=data_bill.data_bill_cfop_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=data_bill.data_bill_project_id ";
                $sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_bill.data_bill_data_dir_id ";
                $sql .= "LEFT JOIN data_cost ON data_cost_id=data_bill_data_cost_id ";
                $sql .= "WHERE YEAR(data_bill.data_bill_date)=:year ";
                $sql .= "AND MONTH(data_bill.data_bill_date)=:month ";
                $sql .= "AND ROUND(data_bill.data_bill_billed_cost,2)>=:minimal_bill ";
		$sql .= "AND cfops.cfop_billtype=:billtype ";
                $sql .= "ORDER BY `CFOP` ASC, `ACTIVITY CODE` ASC";
		$parameters = array(
                        ':month'=>$month,
                        ':year'=>$year,
			':billtype'=>project::BILLTYPE_CFOP,
                        ':minimal_bill'=>$minimal_bill
                );
		$data_result = $db->query($sql,$parameters);


		$total_bill = 0;
		foreach ($data_result as $values) {
                        $total_bill += $values['COST'];
                }

                $first_row = array(array('DATE'=>$month . "/" . $year,
                        'NAME'=>'IGB Biocluster Data',
                        'CFOP'=>settings::get_boa_cfop(),
                        'ACTIVITY CODE'=>'',
                        'COST'=>"-" . $total_bill,
			'DESCRIPTION'=>'',
			));

		return array_merge($first_row,$data_result);			
        }

	public static function get_data_custom_bill($db,$month,$year,$minimal_bill = 0.01) {
                $sql = "SELECT CONCAT(:month '/' :year)  as 'DATE', ";
                $sql .= "projects.project_name as 'NAME', ";
                $sql .= "ROUND(data_bill.data_bill_billed_cost,2) as 'COST', ";
                $sql .= "CONCAT('Biocluster Data - ',data_dir.data_dir_path) as 'DESCRIPTION', ";
		$sql .= "cfops.cfop_custom_description as 'PAYMENT DESCRIPTION' ";
                $sql .= "FROM data_bill ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=data_bill.data_bill_cfop_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=data_bill.data_bill_project_id ";
                $sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_bill.data_bill_data_dir_id ";
                $sql .= "LEFT JOIN data_cost ON data_cost_id=data_bill_data_cost_id ";
                $sql .= "WHERE YEAR(data_bill.data_bill_date)=:year ";
                $sql .= "AND MONTH(data_bill.data_bill_date)=:month ";
                $sql .= "AND ROUND(data_bill.data_bill_billed_cost,2)>=:minimal_bill ";
		$sql .= "AND cfops.cfop_billtype=:billtype ";
                $sql .= "ORDER BY 'NAME' ASC";
                $parameters = array(
                        ':month'=>$month,
                        ':year'=>$year,
                        ':billtype'=>project::BILLTYPE_CFOP,
                        ':minimal_bill'=>$minimal_bill,
			':billtype'=>project::BILLTYPE_CUSTOM
                );
                $data_result = $db->query($sql,$parameters);
		return $data_result;
        }

	public static function get_data_fbs_bill($db,$month,$year,$minimal_bill = 0.01) {
		$sql = "SELECT 'IGB' as 'AreaCode','CNRG' as 'FacilityCode', ";
		$sql .= "'' as 'LabCode', IF(users.user_supervisor <> 0,CONCAT(supervisors.user_lastname,', ',supervisors.user_firstname),CONCAT(users.user_lastname,', ',users.user_firstname)) as 'LabName',  ";
		$sql .= "CONCAT(users.user_firstname,users.user_lastname) as 'RequestedBy', ";
		$sql .= "users.user_name as 'NAME', CONCAT(cfops.cfop_value,IF(cfops.cfop_activity <> '','-',''),cfops.cfop_activity) as 'CFOP', ";
		$sql .= "'BIOCLUSTER' as 'SKU_Code', CONCAT(:month,'-',:year) as 'UsageDate', ";
		$sql .= "'1.000' as 'Quantity', ROUND(data_bill.data_bill_billed_cost,2) as 'UnitPriceOverride', ";
		$sql .= "CONCAT('Biocluster Data - ',SUBSTRING_INDEX(data_dir.data_dir_path,'/',-1)) as 'PrintableComments', ";
		$sql .= "'' as 'UsageRef', '' as 'OrderRef', '' as 'PO_Ref','' as 'PayAlias', ";
		$sql .= "'' as 'bNonBillable','' as 'NonPrintableComments' ";
               	$sql .= "FROM data_bill ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=data_bill.data_bill_cfop_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=data_bill.data_bill_project_id ";
                $sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_bill.data_bill_data_dir_id ";
                $sql .= "LEFT JOIN data_cost ON data_cost_id=data_bill_data_cost_id ";
		$sql .= "LEFT JOIN users ON users.user_id=projects.project_owner ";
		$sql .= "LEFT JOIN users AS supervisors ON supervisors.user_id=users.user_supervisor ";
                $sql .= "WHERE YEAR(data_bill.data_bill_date)=:year ";
                $sql .= "AND MONTH(data_bill.data_bill_date)=:month ";
		$sql .= "AND ROUND(data_bill.data_bill_billed_cost,2)>=:minimal_bill ";
		$sql .= "AND cfops.cfop_billtype=:billtype ";
                $sql .= "ORDER BY LabName ASC";

                $parameters = array(
                        ':month'=>$month,
                        ':year'=>$year,
                        ':terabytes'=>data_functions::CONVERT_TERABYTES,
			':minimal_bill'=>$minimal_bill,
			':billtype'=>project::BILLTYPE_CFOP
                );
                $report = $db->query($sql,$parameters);
		$fbs_customers = functions::get_fbs_labcodes();
                foreach ($report as &$record) {
                        for ($i=0; $i<count($fbs_customers); $i++) {
                                if (trim($record['LabName']) == trim($fbs_customers[$i]['CustomerDirectoryName'])) {
                                        $record['LabCode'] = $fbs_customers[$i]['CustomerCode'];
                                        break;
                                }

                        }

                }
                return $report;

        }

	public static function get_existing_dirs() {
		$root_dirs = settings::get_root_data_dirs();
		
		$existing_dirs = array();
		foreach ($root_dirs as $dir) {
			
			$found_dirs = array();
			$found_dirs = array_diff(scandir($dir), array('..','.'));
			foreach ($found_dirs as &$value) {
				$value = $dir . "/" . $value;
			}
			if (count($found_dirs)) {
				$existing_dirs = array_merge($existing_dirs,$found_dirs);
			}
			
			
		}
		return $existing_dirs;
		
	}
	public static function get_unmonitored_dirs($db) {
		$full_monitored_dirs = self::get_all_directories($db);

		$existing_dirs = self::get_existing_dirs();
		$monitored_dirs = array();
		foreach ($full_monitored_dirs as $dir) {
			array_push($monitored_dirs,$dir['data_dir_path']);
			
		}
		
		$unmonitored_dirs = array_diff($existing_dirs,$monitored_dirs);
		return $unmonitored_dirs;
		
		
		
	}

	public static function bytes_to_terabytes($bytes = 0) {
                return round($bytes / self::CONVERT_TERABYTES,3);

        }
        public static function bytes_to_gigabytes($bytes = 0) {
                return round($bytes / self::CONVERT_GIGABYTES,3);
        }

	public static function get_minimal_year($db) {
                $sql = "SELECT MIN(YEAR(data_bill_date)) as year ";
                $sql .= "FROM data_bill ";
                $result = $db->query($sql);
                if (count($result)) {
                        return $result[0]['year'];
                }
                return date('Y');

        }

}

?>
