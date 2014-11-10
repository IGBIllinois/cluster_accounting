<?php

class data_functions {

	public static function add_data_usage($db,$data_dir_id,$data) {
	        $backup = explode("\t",$data[0]);
        	$no_backup = explode("\t",$data[1]);
		$data_dir = new data_dir($db,$data_dir_id);		
	        $data_usage = new data_usage($db);
		$backup_msg = $data_dir->get_directory() . ": Backup - " . $backup[1] . " bytes";
		$no_backup_msg = $data_dir->get_directory() . ": No Backup - " . $no_backup[1] . " bytes";
		functions::log_message($backup_msg);
		functions::log_message($no_backup_msg);
        	$data_usage->create($data_dir_id,"backup",$backup[1],$backup[2]);
	        $data_usage->create($data_dir_id,"no_backup",$no_backup[1],$no_backup[2]);
	}

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
		$sql .= "data_cost.data_cost_dir as directory, ";
		$sql .= "ROUND(data_cost_value,2) as cost, ";
		$sql .= "data_cost_time as time ";
		$sql .= "FROM data_cost ";
		$sql .= "WHERE data_cost_enabled='1' ";
		$sql .= "ORDER BY directory ";
		return $db->query($sql);	
	}


	public static function get_data_bill($db,$month,$year,$minimum_bill = 0) {
		$sql = "SELECT data_dir.data_dir_path as 'Directory', ";
	        $sql .= "ROUND(data_usage_bytes / 1099511627776,3) as 'Terabytes', ";
        	$sql .= "ROUND(data_cost_value,2) as 'Rate ($/Terabyte)', ";
	        $sql .= "data_cost_dir as 'Data Type', ";
        	$sql .= "ROUND(data_usage_total_cost,2) as 'Total Cost', ";
	        $sql .= "ROUND(data_usage_billed_cost,2) as 'Billed Cost', ";
        	$sql .= "projects.project_name as 'Project', ";
	        $sql .= "cfops.cfop_value as 'CFOP', cfops.cfop_activity as 'Activity Code' ";
        	$sql .= "FROM data_usage ";
	        $sql .= "LEFT JOIN cfops ON cfops.cfop_id=data_usage.data_usage_cfop_id ";
        	$sql .= "LEFT JOIN projects ON projects.project_id=data_usage.data_usage_project_id ";
	        $sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_usage.data_usage_data_dir_id ";
        	$sql .= "LEFT JOIN data_cost ON data_cost_id=data_usage_data_cost_id ";
	        $sql .= "WHERE YEAR(data_usage.data_usage_time)='" . $year . "' ";
        	$sql .= "AND MONTH(data_usage.data_usage_time)='" . $month . "' ";
	        $sql .= "AND data_usage_total_cost > 0.01 ";
		$sql .= "ORDER BY Directory ASC";
        	return $db->query($sql);
	}

	public static function get_existing_dirs() {
		$root_dirs = settings::get_root_data_dirs();
		
		$existing_dirs = array();
		foreach ($root_dirs as $dir) {
			
			$found_dirs = array();
			$found_dirs = array_diff(scandir($dir), array('..', '.'));
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
}
?>
