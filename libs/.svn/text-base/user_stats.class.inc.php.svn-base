<?php

class user_stats {

	////////////////Private Variables//////////
	private $db; //mysql database object
	private $user_id;
	private $start_date;
	private $end_date;
	private $generic_results = array();
	////////////////Public Functions///////////
	public function __construct($db,$user_id,$start_date,$end_date) {
		$this->db = $db;
		$this->user_id = $user_id;
		$this->start_date = $start_date;
		$this->end_date = $end_date;

	}

	public function __destruct() {
	}
	public function get_num_jobs($format = 0) {
		if (!count($this->generic_results)) {
                        $this->generic_query();
                }
                $num_jobs = $this->generic_results[0]['num_jobs'];
                if (!$num_jobs) {
                        $num_jobs = 0;
                }
                if ($format) {
			return number_format($num_jobs);
		}
		return $num_jobs;
	}

	public function get_total_cost($format = 0) {
                if (!count($this->generic_results)) {
                        $this->generic_query();
                }
                $total_cost = $this->generic_results[0]['total_cost'];
                if (!$total_cost) {
                        $total_cost = 0;
                }
		if ($format) {
			return number_format($total_cost,2);
		}	
                return $total_cost;
	}
	public function get_billed_cost($format = 0) {
                if (!count($this->generic_results)) {
                        $this->generic_query();
                }
                $billed_cost = $this->generic_results[0]['billed_cost'];
                if (!$billed_cost) {
                        $billed_cost = 0;
                }
		if ($format) {
			return number_format($billed_cost,2);
		}
		return $billed_cost;
	}

	public function get_avg_elapsed_time() {
		if (!count($this->generic_results)) {
			$this->generic_query();	
		}
		$avg_elapsed_time = $this->generic_results[0]['avg_elapsed_time'];
		if (!$avg_elapased_time) {
			$avg_elapsed_time = "00:00:00";
		}
		return $avg_elapsed_time;

	}

	public function get_max_job_length() {
		if (!count($this->generic_results)) {
                        $this->generic_query();
                }
                $max_job_length = $this->generic_results[0]['max_job_length'];
                if (!$max_job_length) {
                        $max_job_length = "00:00:00";
                }
                return $max_job_length;
	}

	public function get_num_completed_jobs() {
                if (!count($this->generic_results)) {
                        $this->generic_query();
                }
                $num_job_complete = $this->generic_results[0]['num_job_complete'];
                if (!$num_job_complete) {
                        $num_job_complete = "0";
                }
                return $num_job_complete;


	}
	public function get_num_failed_jobs() {
                if (!count($this->generic_results)) {
                        $this->generic_query();
                }
                $num_job_failed = $this->generic_results[0]['num_job_failed'];
                if (!$num_job_failed) {
                        $num_job_failed = "0";
                }
                return $num_job_failed;


	}
	////////////////////Private Functions////////////////////
	private function generic_query() {
		$sql = "SELECT ROUND(SUM(jobs.job_billed_cost),2) as billed_cost, ";
		$sql .= "ROUND(SUM(jobs.job_total_cost),2) as total_cost, ";
		$sql .= "SEC_TO_TIME(MAX(TIME_TO_SEC(jobs.job_ru_wallclock))) as max_job_length, ";
		$sql .= "SEC_TO_TIME(AVG(TIME_TO_SEC(jobs.job_ru_wallclock))) as avg_job_length, ";
		$sql .= "COUNT(1) as num_jobs, ";
		$sql .= "SUM(IF(job_exit_status=0,1,0)) as num_job_complete, ";
		$sql .= "SUM(IF(job_exit_status!=0,1,0)) as num_job_failed, ";
		$sql .= "SEC_TO_TIME(AVG(TIME_TO_SEC(job_start_time)-TIME_TO_SEC(job_submission_time))) AS avg_wait ";
		$sql .= "FROM jobs ";
		$sql .= "WHERE DATE(jobs.job_end_time) BETWEEN '" . $this->start_date . "' AND '" . $this->end_date . "' ";
		$sql .= "AND jobs.job_user_id='" . $this->user_id . "'";
		$this->generic_results = $this->db->query($sql);




	}

}

?>
