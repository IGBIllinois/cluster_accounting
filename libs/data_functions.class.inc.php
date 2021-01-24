<?php

class data_functions {

        const convert_terabytes = 1099511627776;
        const convert_gigabytes = 1073741824;

	public static function get_directories($db,$default = 1,$start,$count) {
		$sql = "SELECT data_dir.*, projects.project_name, projects.project_id ";
		$sql .= "FROM data_dir ";
		$sql .= "LEFT JOIN projects ON projects.project_id=data_dir.data_dir_project_id ";
		$sql .= "WHERE data_dir_enabled='1' ";
		$sql .= "AND data_dir_default='" . $default . "' ";
	
		$sql .= "ORDER BY data_dir.data_dir_path ASC ";
		if ($count != 0) {
			$sql .= "LIMIT " . $start . "," . $count;
		}
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
		$sql .= "AND data_dir_default='" . $default . "'";
		$result = $db->query($sql);
		return $result[0]['count'];
	}

	public static function get_data_costs($db) {
		$sql = "SELECT data_cost.data_cost_id as id, ";
		$sql .= "data_cost.data_cost_type as type, ";
		$sql .= "ROUND(data_cost_value,2) as cost, ";
		$sql .= "data_cost_time as time ";
		$sql .= "FROM data_cost ";
		$sql .= "WHERE data_cost_enabled='1' ";
		$sql .= "ORDER BY type ";
		return $db->query($sql);	
	}

	public static function get_current_data_cost_by_type($db,$type) {
		 $sql = "SELECT data_cost.data_cost_id as id, ";
                $sql .= "data_cost.data_cost_type as type, ";
                $sql .= "ROUND(data_cost_value,2) as cost, ";
                $sql .= "data_cost_time as time ";
                $sql .= "FROM data_cost ";
                $sql .= "WHERE data_cost_enabled='1' ";
		$sql .= "AND data_cost_type='" . $type . "'";
                $sql .= "ORDER BY type ";
		$result = $db->query($sql);
		if (count($result) == 1) {
			return $result[0];
		}
                return false;


	}
	public static function get_data_bill($db,$month,$year,$minimum_bill = 0.00) {
		$sql = "SELECT data_dir.data_dir_path as 'Directory', ";
	        $sql .= "ROUND(data_bill.data_bill_avg_bytes / 1099511627776,3) as 'Terabytes', ";
        	$sql .= "ROUND(data_cost.data_cost_value,2) as 'Rate ($/Terabyte)', ";
	        $sql .= "data_cost.data_cost_type as 'Data Type', ";
        	$sql .= "ROUND(data_bill.data_bill_total_cost,2) as 'Total Cost', ";
	        $sql .= "ROUND(data_bill.data_bill_billed_cost,2) as 'Billed Cost', ";
        	$sql .= "projects.project_name as 'Project', ";
	        $sql .= "cfops.cfop_value as 'CFOP', cfops.cfop_activity as 'Activity Code' ";
        	$sql .= "FROM data_bill ";
	        $sql .= "LEFT JOIN cfops ON cfops.cfop_id=data_bill.data_bill_cfop_id ";
        	$sql .= "LEFT JOIN projects ON projects.project_id=data_bill.data_bill_project_id ";
	        $sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_bill.data_bill_data_dir_id ";
        	$sql .= "LEFT JOIN data_cost ON data_cost_id=data_bill_data_cost_id ";
	        $sql .= "WHERE YEAR(data_bill.data_bill_date)='" . $year . "' ";
        	$sql .= "AND MONTH(data_bill.data_bill_date)='" . $month . "' ";
	        $sql .= "AND ROUND(data_bill.data_bill_total_cost,2)>'" . $minimum_bill . "' ";
		$sql .= "ORDER BY Directory ASC";
        	return $db->query($sql);
	}

        public static function get_data_boa_bill($db,$month,$year,$minimal_bill = 0.00) {
                $sql = "SELECT '' as 'DATE', ";
		$sql .= "projects.project_name as 'NAME', ";
		$sql .= "cfops.cfop_value as 'CFOP', ";
		$sql .= "cfops.cfop_activity as 'ACTIVITY CODE', ";	
                $sql .= "ROUND(data_bill.data_bill_billed_cost,2) as 'COST', ";
		$sql .= "CONCAT('Biocluster Data - ',data_dir.data_dir_path) as 'DESCRIPTION' ";
                $sql .= "FROM data_bill ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=data_bill.data_bill_cfop_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=data_bill.data_bill_project_id ";
                $sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_bill.data_bill_data_dir_id ";
                $sql .= "LEFT JOIN data_cost ON data_cost_id=data_bill_data_cost_id ";
                $sql .= "WHERE YEAR(data_bill.data_bill_date)='" . $year . "' ";
                $sql .= "AND MONTH(data_bill.data_bill_date)='" . $month . "' ";
                $sql .= "AND ROUND(data_bill.data_bill_billed_cost,2)>'" . $minimal_bill . "' ";
                $sql .= "ORDER BY `CFOP` ASC, `ACTIVITY CODE` ASC";
		$data_result = $db->query($sql);


		$total_bill = 0;
		foreach ($data_result as $num => $values) {
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

	public static function get_existing_dirs() {
		$root_dirs = settings::get_root_data_dirs();
		
		$existing_dirs = array();
		foreach ($root_dirs as $dir) {
			
			$found_dirs = array();
			$found_dirs = array_diff(scandir($dir), array('..','.'));
			foreach ($found_dirs as $key=>&$value) {
				if (is_link($dir . "/" . $value)) {
					unset($found_dirs[$key]);
				}
				else {
					$value = $dir . "/" . $value;
				}
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
                return round($bytes / self::convert_terabytes,3);

        }
        public static function bytes_to_gigabytes($bytes = 0) {
                return round($bytes / self::convert_gigabytes,3);
        }

}
?>
