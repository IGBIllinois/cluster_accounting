<?php
class data_stats {


	public static function get_total_cost($db,$start_date,$end_date,$format = 0) {
	        $sql = "SELECT SUM(data_bill_total_cost) as total_cost ";
        	$sql .= "FROM data_bill ";
	        $sql .= "WHERE data_bill_date BETWEEN '" . $start_date . "' AND '" . $end_date . "' ";
	        $result = $db->query($sql);
		$cost = 0;
	        if ($result) {
			$cost = $result[0]['total_cost'];
			if ($format) {
				$cost = number_format($result[0]['total_cost'],2);
			}
		}
		return $cost;
	}

	public static function get_billed_cost($db,$start_date,$end_date,$format = 0) {
		$sql = "SELECT SUM(data_bill_billed_cost) as billed_cost ";
                $sql .= "FROM data_bill ";
		$sql .= "WHERE data_bill_date BETWEEN '" . $start_date . "' AND '" . $end_date . "' ";
                $result = $db->query($sql);
                $cost = 0;
                if ($result) {
                        $cost = $result[0]['billed_cost'];
                	if ($format) {
                        	$cost = number_format($result[0]['billed_cost'],2);
	                }
		}
                return $cost;

	}

        public static function get_usage_per_month($db,$year,$directory_type = 0,$project_id = "") {
		$bytes_per_terabyte = 1099511627776;
                $sql = "SELECT MONTH(data_usage.data_usage_time) as month, ";
		$sql .= "ROUND(SUM(data_usage.data_usage_bytes)/" . $bytes_per_terabyte . ",2) as terabyte ";
                $sql .= "FROM data_usage ";
		$sql .= "LEFT JOIN data_cost ON data_cost.data_cost_id=data_usage.data_usage_data_cost_id ";
                $sql .= "WHERE YEAR(data_usage_time)='" . $year . "' ";
		if ($project_id) {
			$sql .= "AND data_usage.data_usage_project_id='" . $project_id . "' ";
		}
		if ($directory_type) {
			$sql .= "AND data_cost.data_cost_dir='" . $directory_type . "' ";
		}
                $sql .= "GROUP BY MONTH(data_usage.data_usage_time) ";
		$sql .= "ORDER BY MONTH(data_usage.data_usage_time) ASC ";
		$result = $db->query($sql);
		return statistics::get_month_array($result,"month","terabyte");
	}

        public static function get_project_usage_per_month($db,$year,$directory_type = 0) {
                $bytes_per_terabyte = 1099511627776;
                $sql = "SELECT MONTH(data_usage.data_usage_time) as month, ";
		$sql .= "projects.project_name as project, ";
                $sql .= "ROUND(SUM(data_usage.data_usage_bytes)/" . $bytes_per_terabyte . ",2) as terabyte ";
                $sql .= "FROM data_usage ";
                $sql .= "LEFT JOIN data_cost ON data_cost.data_cost_id=data_usage.data_usage_data_cost_id ";
		$sql .= "LEFT JOIN projects ON projects.project_id=data_usage.data_usage_project_id ";
                $sql .= "WHERE YEAR(data_usage_time)='" . $year . "' ";
                if ($directory_type) {
                        $sql .= "AND data_cost.data_cost_dir='" . $directory_type . "' ";
                }
                $sql .= "GROUP BY project,MONTH(data_usage.data_usage_time) ";
                $sql .= "ORDER BY MONTH(data_usage.data_usage_time) ASC ";

                $result = $db->query($sql);
                return statistics::get_month_array($result,"month","terabyte");
        }
        public static function get_project_usage($db,$start_date,$end_date,$directory_type = 0) {
                $bytes_per_terabyte = 1099511627776;
                $sql = "SELECT projects.project_name as project, ";
                $sql .= "ROUND(SUM(data_usage.data_usage_bytes)/" . $bytes_per_terabyte . ",2) as terabyte ";
                $sql .= "FROM data_usage ";
                $sql .= "LEFT JOIN data_cost ON data_cost.data_cost_id=data_usage.data_usage_data_cost_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=data_usage.data_usage_project_id ";
		$sql .= "WHERE data_usage_time BETWEEN '" . $start_date . "' AND '" . $end_date . "' ";
                if ($directory_type) {
                        $sql .= "AND data_cost.data_cost_dir='" . $directory_type . "' ";
                }
                $sql .= "GROUP BY project ";
                $sql .= "ORDER BY terabyte DESC ";
                return $db->query($sql);
        }
	public static function get_top_data_usage($db,$start_date,$end_date,$top) {
                $data_usage = self::get_project_usage($db,$start_date,$end_date);
		$total_terabytes = 0;
		$top_terabytes = 0;
                if (count($data_usage) > $top) {
                        $i=0;
                        foreach ($data_usage as $data) {
                                if ($i<$top) {
                                        $top_terabytes += $data['terabyte'];
                                }
                                $total_terabytes += $data['terabyte'];
                                $i++;
                        }
                        $result = array_slice($data_usage,0,$top,TRUE);
                        $result[$top]['project'] = "Other";
                        $result[$top]['terabyte'] = $total_terabytes - $top_terabytes;
                }
                else {
                        $result = $data_usage;

                }
                return $result;
        }

}
?>
