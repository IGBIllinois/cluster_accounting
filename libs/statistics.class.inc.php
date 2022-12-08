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


	public function get_total_cost($start_date,$end_date,$format = 0) {
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


	public function get_total_billed_cost($start_date,$end_date,$format = 0) {
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

		$sql = "SELECT count(1) as count ";
		$sql .= "FROM jobs ";
		$sql .= "WHERE DATE(job_end_time) BETWEEN :start_date AND :end_date";
		$parameters = array(
			':start_date'=>$start_date,
			':end_date'=>$end_date
		);
		$result = $this->db->query($sql.$parameters);
		$num_jobs = $result[0]['count'];
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
                        ':start_date'=>$start_date,
                        ':end_date'=>$end_date,
			':user_id'=>$user_id
                );
		$result = $this->db->query($sql,$parameters);
		return $result[0]['count'];
	}

	public function get_jobs_per_month($year,$user_id = 0) {
		$parameters = array(
                        ':year'=>$year
                );

		$sql = "SELECT MONTH(jobs.job_end_time) as month,  ";
		$sql .= "count(1) as num_jobs ";
		$sql .= "FROM jobs ";
		$sql .= "WHERE YEAR(jobs.job_end_time)=:year ";
		if ($user_id) {
                        $sql .= "AND jobs.job_user_id=:user_id ";
			$parameters[':user_id'] = $user_id;
                }
		$sql .= "GROUP BY MONTH(jobs.job_end_time) ";
		$sql .= "ORDER BY MONTH(jobs.job_end_time) ASC";
		$result = $this->db->query($sql,$parameters);
		
		return $this->get_month_array($result,"month","num_jobs");
	}
	
	public function get_job_billed_cost_per_month($year,$user_id = 0) {
		$parameters = array(
                        ':year'=>$year
                );
                $sql = "SELECT MONTH(job_end_time) as month,ROUND(SUM(job_billed_cost),2) as billed_cost ";
                $sql .= "FROM jobs ";
                $sql .= "WHERE YEAR(job_end_time)=:year ";
                if ($user_id) {
                        $sql .= "AND jobs.job_user_id=:user_id ";
			$parameters[':user_id'] = $user_id;
                }

                $sql .= "GROUP BY MONTH(job_end_time)";
		$sql .= "ORDER BY MONTH(job_end_time) ASC";
                $result = $this->db->query($sql,$parameters);
		return $this->get_month_array($result,"month","billed_cost");


	}
        public function get_job_total_cost_per_month($year,$user_id = 0) {
		$parameters = array(
                        ':year'=>$year
                );

                $sql = "SELECT MONTH(job_end_time) as month,ROUND(SUM(job_total_cost),2) as total_cost ";
                $sql .= "FROM jobs ";
                $sql .= "WHERE YEAR(job_end_time)=:year ";
                if ($user_id) {
                        $sql .= "AND jobs.job_user_id=:user_id ";
			$parameters[':user_id'] = $user_id;
                }

                $sql .= "GROUP BY MONTH(job_end_time)";
                $result = $this->db->query($sql,$parameters);
                return $this->get_month_array($result,"month","total_cost");


        }

	public function get_longest_job($start_date,$end_date) {
		$sql = "SELECT SEC_TO_TIME(MAX(TIME_TO_SEC(jobs.job_ru_wallclock))) as max_job_length ";
		$sql .= "FROM jobs ";
		$sql .= "WHERE DATE(job_end_time) BETWEEN :start_date AND :end_date";
		$parameters = array(
			':start_date'=>$start_date,
			':end_date'=>$end_date
		);
		$result = $this->db->query($sql,$parameters);
		$max_job_length = $result[0]['max_job_length'];
		if (!$max_job_length) {
			$max_job_length = "00:00:00";
		}
		return $max_job_length;
	}

	public function get_avg_job($start_date,$end_date) {
		$sql = "SELECT SEC_TO_TIME(ROUND(AVG(TIME_TO_SEC(jobs.job_ru_wallclock)))) as avg_job_length ";
		$sql .= "FROM jobs ";
		$sql .= "WHERE DATE(job_end_time) BETWEEN :start_date AND :end_date";
		$parameters = array(
			':start_date'=>$start_date,
                        ':end_date'=>$end_date
		);
		$result = $this->db->query($sql,$parameters);
		$avg_job_length = $result[0]['avg_job_length'];
		if (!$avg_job_length) {
			$avg_job_length = "00:00:00";
		}
		return $avg_job_length;
	}


	//get_avg_wait()
	//calculates the average of the time between when the job is submitted to the queue till it starts to run.
	//$state_date - start date
	//$end_date - end date
	//returns number of secs
	public function get_avg_wait($start_date,$end_date) {
		$sql = "SELECT SEC_TO_TIME(ROUND(AVG(TIME_TO_SEC(TIMEDIFF(job_start_time,job_submission_time))))) AS avg_wait ";
		$sql .= "FROM jobs ";
		$sql .= "WHERE DATE(job_end_time) BETWEEN :start_date AND :end_date";
		$parameters = array(
                        ':start_date'=>$start_date,
                        ':end_date'=>$end_date
                );

		$result = $this->db->query($sql,$parameters);
		$avg_wait = $result[0]['avg_wait'];
		if (!$avg_wait) {
			$avg_wait = "00:00:00";
		}
		return $avg_wait;
	}

	//get_wait_time()
	//returns the difference between start_time and submission_time for each job in a specified period.
	//$start_date - start date of period
	//$end_date - end date of period
	//returns array of wait time for each job in seconds.
	public function get_wait_time($start_date,$end_date) {
		$sql = "SELECT TIME_TO_SEC(TIMEDIFF(job_start_time,job_submission_time)) as wait_time ";
		$sql .= "FROM jobs ";
		$sql .= "WHERE DATE(job_end_time) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
		$sql_result =  $this->db->query($sql);
		$result = array();
		foreach($sql_result as $value) {
			array_push($result,$value['wait_time']);

		}
		return $result;
	}

	public function get_jobs_summary($start_date,$end_date,$sort = "user_name", $direction = "ASC") {

		$sql = "SELECT ROUND(SUM(jobs.job_total_cost),2) as total_cost, ";
		$sql .= "ROUND(SUM(jobs.job_billed_cost),2) as billed_cost, ";
		$sql .= "COUNT(1) as num_jobs, ";
		$sql .= "users.user_name as user_name, users.user_full_name as full_name ";
		$sql .= "FROM jobs ";
		$sql .= "LEFT JOIN users ON users.user_id=jobs.job_user_id ";
		$sql .= "WHERE DATE(jobs.job_end_time) BETWEEN '" . $start_date ."' AND '" . $end_date . "' ";
		$sql .= "GROUP BY users.user_name ";
		$sql .= "ORDER BY " . $sort . " " . $direction;

		return $this->db->query($sql);
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
