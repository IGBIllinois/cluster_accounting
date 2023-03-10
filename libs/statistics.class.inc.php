<?php

class statistics {


	////////////////Private Variables//////////
	private $db; //mysql database object


	////////////////Public Functions///////////
	public function __construct(&$db) {
		$this->db = $db;

	}

	public function __destruct() {
	}


	public function get_job_total_cost($start_date,$end_date,$format = 0) {
		$sql = "SELECT ROUND(SUM(job_bill_total_cost),2) AS total_cost ";
		$sql .= "FROM job_bill ";
		$sql .= "WHERE DATE(job_bill_date) BETWEEN :start_date AND :end_date";
		
		$parameters = array(
			':start_date'=>$start_date->format("Y-m-d H:i:s"),
			':end_date'=>$end_date->format("Y-m-d H:i:s")
		);
		$result = $this->db->query($sql,$parameters);
		$total_cost = $result[0]['total_cost'];
		if ($result[0]['total_cost'] == "") {
			$total_cost = "0.00";
		}
		if ($format) {
			$total_cost = number_format($total_cost,2);
		}
		return $total_cost;
	}


	public function get_job_total_billed_cost($start_date,$end_date,$format = 0) {
		$sql = "SELECT ROUND(SUM(job_bill_billed_cost),2) AS billed_cost ";
		$sql .= "FROM job_bill ";
		$sql .= "WHERE DATE(job_bill_date) BETWEEN :start_date AND :end_date";
		$parameters = array(
                        ':start_date'=>$start_date->format("Y-m-d H:i:s"),
                        ':end_date'=>$end_date->format("Y-m-d H:i:s")
                );
		$result = $this->db->query($sql,$parameters);
		$billed_cost = $result[0]['billed_cost'];
		if ($result[0]['billed_cost'] == "") {
			$billed_cost = "0.00";
		}
		if ($format) {
			$billed_cost = number_format($billed_cost,2);
		}
		return $billed_cost;
	}


	public function get_num_jobs($start_date,$end_date, $format = 0) {

		$sql = "SELECT SUM(job_bill_num_jobs) as num_jobs ";
		$sql .= "FROM job_bill ";
		$sql .= "WHERE DATE(job_bill_date) BETWEEN :start_date AND :end_date";
		$parameters = array(
			':start_date'=>$start_date->format("Y-m-d H:i:s"),
			':end_date'=>$end_date->format("Y-m-d H:i:s")
		);
		$result = $this->db->query($sql,$parameters);
		$num_jobs = $result[0]['num_jobs'];
		if ($format == 1) {
			$num_jobs = number_format($num_jobs,0);
		}
		return $num_jobs;
	}
	public function get_number_jobs_by_user($start_date,$end_date,$user_id) {
		$sql = "SELECT count(1) as count FROM jobs ";
		$sql .= "WHERE DATE(jobs.job_end_time) BETWEEN :start_date AND :end_date ";
		$sql .= "AND jobs.job_user_id=:user_id";
		$parameters = array(
                        ':start_date'=>$start_date->format("Y-m-d H:i:s"),
                        ':end_date'=>$end_date->format("Y-m-d H:i:s"),
			':user_id'=>$user_id
                );
		$result = $this->db->query($sql,$parameters);
		return $result[0]['count'];
	}

	public function get_jobs_per_month($year,$user_id = 0) {
		$parameters = array(
                        ':year'=>$year
                );

		$sql = "SELECT MONTH(job_bill.job_bill_date) as month,  ";
		$sql .= "SUM(job_bill.job_bill_num_jobs) as num_jobs ";
		$sql .= "FROM job_bill ";
		$sql .= "WHERE YEAR(job_bill.job_bill_date)=:year ";
		if ($user_id) {
                        $sql .= "AND job_bill.job_bill_user_id=:user_id ";
			$parameters[':user_id'] = $user_id;
                }
		$sql .= "GROUP BY MONTH(job_bill.job_bill_date) ";
		$sql .= "ORDER BY MONTH(job_bill.job_bill_date) ASC";
		$result = $this->db->query($sql,$parameters);
		
		return $this->get_month_array($result,"month","num_jobs");
	}
	
	public function get_job_billed_cost_per_month($year,$user_id = 0) {
		$parameters = array(
                        ':year'=>$year
                );
                $sql = "SELECT MONTH(job_bill_date) as month, ";
		$sql .= "ROUND(SUM(job_bill_billed_cost),2) as billed_cost ";
                $sql .= "FROM job_bill ";
                $sql .= "WHERE YEAR(job_bill_date)=:year ";
                if ($user_id) {
                        $sql .= "AND job_bill.job_bill_user_id=:user_id ";
			$parameters[':user_id'] = $user_id;
                }

                $sql .= "GROUP BY MONTH(job_bill_date)";
		$sql .= "ORDER BY MONTH(job_bill_date) ASC";
                $result = $this->db->query($sql,$parameters);
		return $this->get_month_array($result,"month","billed_cost");


	}
        public function get_job_total_cost_per_month($year,$user_id = 0) {
		$parameters = array(
                        ':year'=>$year
                );

                $sql = "SELECT MONTH(job_bill_date) as month, ";
		$sql .= "ROUND(SUM(job_bill_total_cost),2) as total_cost ";
                $sql .= "FROM job_bill ";
                $sql .= "WHERE YEAR(job_bill_date)=:year ";
                if ($user_id) {
                        $sql .= "AND job_bill.job_bill_user_id=:user_id ";
			$parameters[':user_id'] = $user_id;
                }

                $sql .= "GROUP BY MONTH(job_bill_date)";
		$sql .= "ORDER BY MONTH(job_bill_date) ASC";
                $result = $this->db->query($sql,$parameters);
                return $this->get_month_array($result,"month","total_cost");


        }


	public function get_jobs_summary($start_date,$end_date,$sort = "user_name", $direction = "ASC") {

		$sql = "SELECT ROUND(SUM(job_bill.job_bill_total_cost),2) as total_cost, ";
		$sql .= "ROUND(SUM(job_bill.job_bill_billed_cost),2) as billed_cost, ";
		$sql .= "SUM(job_bill.job_bill_num_jobs) as num_jobs, ";
		$sql .= "users.user_name as user_name, CONCAT(users.user_firstname,' ',users.user_lastname) as full_name ";
		$sql .= "FROM job_bill ";
		$sql .= "LEFT JOIN users ON users.user_id=job_bill.job_bill_user_id ";
		$sql .= "WHERE DATE(job_bill.job_bill_date) BETWEEN :start_date AND :end_date ";
		$sql .= "GROUP BY users.user_name ";
		$sql .= "ORDER BY " . $sort . " " . $direction;
		$parameters = array(
                        ':start_date'=>$start_date->format("Y-m-d H:i:s"),
                        ':end_date'=>$end_date->format("Y-m-d H:i:s")
                );
		return $this->db->query($sql,$parameters);
	}

	public function get_top_cost_users($start_date,$end_date,$top) {
		$job_summary = $this->get_jobs_summary($start_date,$end_date,"total_cost","DESC");
		$top_cost = 0;
		if (count($job_summary) > $top) {
			$total_cost = 0;
			$i=0;
			foreach ($job_summary as $cost) {
				if ($i<$top) {
					$top_cost += $cost['total_cost'];
				}
				$total_cost += $cost['total_cost'];
				$i++;
			}
			$result = array_slice($job_summary,0,$top,TRUE);
			$result[$top]['user_name'] = "Other";
			$result[$top]['total_cost'] = $total_cost - $top_cost;
		}
		else {
			$result = $job_summary;
				
		}
		return $result;
	}

        public function get_top_billed_cost_users($start_date,$end_date,$top) {
                $job_summary = $this->get_jobs_summary($start_date,$end_date,"billed_cost","DESC");
                $top_cost = 0;
                if (count($job_summary) > $top) {
                        $total_cost = 0;
                        $i=0;
                        foreach ($job_summary as $cost) {
                                if ($i<$top) {
                                        $top_cost += $cost['billed_cost'];
                                }
                                $total_cost += $cost['billed_cost'];
                                $i++;
                        }
                        $result = array_slice($job_summary,0,$top,TRUE);
                        $result[$top]['user_name'] = "Other";
                        $result[$top]['billed_cost'] = $total_cost - $top_cost;
                }
                else {
                        $result = $job_summary;

                }
                return $result;
        }

	public function get_top_job_users($start_date,$end_date,$top) {
		$job_summary = $this->get_jobs_summary($start_date,$end_date,"num_jobs","DESC");

		if (count($job_summary) > $top) {
			$total_jobs = 0;
			$top_jobs = 0;
			$i=0;
			foreach ($job_summary as $job) {
				if ($i<$top) {
					$top_jobs += $job['num_jobs'];
				}
				$total_jobs += $job['num_jobs'];
				$i++;
			}
			$result = array_slice($job_summary,0,$top,TRUE);
			$result[$top]['user_name'] = "Other";
			$result[$top]['num_jobs'] = $total_jobs - $top_jobs;
		}
		else {
			$result = $job_summary;
				
		}
		return $result;

	}

	public static function get_month_array($data,$month_column,$data_column) {
		$new_data = array();
		for($i=1;$i<=12;$i++){
			$exists = false;
			if (count($data) > 0) {
				foreach($data as $row) {
					$month = $row[$month_column];
					if ($month == $i) {
						$month_name = date('F', mktime(0,0,0,$month,1));
						array_push($new_data,array('month_name'=>$month_name,
									$data_column=>$row[$data_column]));
						$exists = true;
						break(1);
					}
				}
			}
			if (!$exists) {
				$month_name = date('F', mktime(0,0,0,$i,1));
				array_push($new_data,array('month_name'=>$month_name,
                                                                        $data_column=>0));
			}
			$exists = false;
		}
		return $new_data;
	}
}

?>
