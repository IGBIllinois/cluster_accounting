<?php
class data_stats {

	public static function get_total_cost($db,$start_date,$end_date,$format = 0) {
	        $sql = "SELECT SUM(data_bill_total_cost) as total_cost ";
        	$sql .= "FROM data_bill ";
	        $sql .= "WHERE data_bill_date BETWEEN :start_date AND :end_date ";
		$parameters = array(
			':start_date'=>$start_date->format("Y-m-d H:i:s"),
			':end_date'=>$end_date->format("Y-m-d H:i:s")
		);
	        $result = $db->query($sql,$parameters);
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
		$sql .= "WHERE data_bill_date BETWEEN :start_date AND :end_date ";
		$parameters = array(
                        ':start_date'=>$start_date->format("Y-m-d H:i:s"),
                        ':end_date'=>$end_date->format("Y-m-d H:i:s")
                );
                $result = $db->query($sql,$parameters);
                $cost = 0;
                if ($result) {
                        $cost = $result[0]['billed_cost'];
                	if ($format) {
                        	$cost = number_format($result[0]['billed_cost'],2);
	                }
		}
                return $cost;

	}

        public static function get_usage_per_month($db,$year,$project_id = "") {
                $sql = "SELECT MONTH(data_bill.data_bill_date) as month, ";
		$sql .= "ROUND(SUM(data_bill.data_bill_avg_bytes)/:terabytes,2) as terabyte ";
                $sql .= "FROM data_bill ";
		$sql .= "LEFT JOIN data_cost ON data_cost.data_cost_id=data_bill.data_bill_data_cost_id ";
                $sql .= "WHERE YEAR(data_bill_date)=:year ";
		$parameters = array(
			':year'=>$year,
			':terabytes'=>data_functions::CONVERT_TERABYTES
		);
		if ($project_id) {
			$sql .= "AND data_bill.data_bill_project_id=:project_id ";
			$parameters[':project_id'] = $project_id;
		}
                $sql .= "GROUP BY MONTH(data_bill.data_bill_date) ";
		$sql .= "ORDER BY MONTH(data_bill.data_bill_date) ASC ";
		
		$result = $db->query($sql,$parameters);
		return statistics::get_month_array($result,"month","terabyte");
	}

        public static function get_project_usage($db,$start_date,$end_date) {
                $sql = "SELECT projects.project_name as project, ";
                $sql .= "ROUND(SUM(data_bill.data_bill_avg_bytes)/:terabytes,2) as terabyte ";
                $sql .= "FROM data_bill ";
                $sql .= "LEFT JOIN data_cost ON data_cost.data_cost_id=data_bill.data_bill_data_cost_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=data_bill.data_bill_project_id ";
		$sql .= "WHERE data_bill_date BETWEEN :start_date AND :end_date ";
                $sql .= "GROUP BY project ";
                $sql .= "ORDER BY terabyte DESC ";
		$parameters = array(
			':start_date'=>$start_date->format("Y-m-d H:i:s"),
			':end_date'=>$end_date->format("Y-m-d H:i:s"),
			':terabytes'=>data_functions::CONVERT_TERABYTES
		);
                return $db->query($sql,$parameters);
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
