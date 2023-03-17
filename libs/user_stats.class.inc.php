<?php

class user_stats {

	////////////////Private Variables//////////
	private $db; //mysql database object
	private $user_id;
	private $month;
	private $year;
	private $generic_results = array();

	////////////////Public Functions///////////
	public function __construct($db,$user_id,$month,$year) {
		$this->db = $db;
		$this->user_id = $user_id;
		$this->month = $month;
		$this->year = $year;

	}

	public function __destruct() {
	}
	public function get_num_jobs($format = 0) {
		if (!count($this->generic_results)) {
                        $this->generic_query();
                }
                $num_jobs = $this->generic_results[0]['Num Jobs'];
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
                $total_cost = $this->generic_results[0]['Total Cost'];
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
                $billed_cost = $this->generic_results[0]['Billed Cost'];
                if (!$billed_cost) {
                        $billed_cost = 0;
                }
		if ($format) {
			return number_format($billed_cost,2);
		}
		return $billed_cost;
	}

	
	////////////////////Private Functions////////////////////
	private function generic_query() {
		$sql = "SELECT users.user_name as 'Username', ";
		$sql .= "job_bill.job_bill_num_jobs as 'Num Jobs', ";
                $sql .= "projects.project_name as 'Project', ";
                $sql .= "queues.queue_name as 'Queue', ";
                $sql .= "queue_cost.queue_cost_cpu as 'Queue CPU Cost (Per Second)', ";
                $sql .= "queue_cost.queue_cost_mem as 'Queue Memory Cost (Per GB)', ";
                $sql .= "ROUND(job_bill.job_bill_total_cost,2) as 'Total Cost', ";
                $sql .= "ROUND(job_bill.job_bill_billed_cost,2) as 'Billed Cost', ";
                $sql .= "cfops.cfop_value as 'CFOP', ";
                $sql .= "cfops.cfop_activity as 'Activity Code' ";
                $sql .= "FROM job_bill ";
                $sql .= "LEFT JOIN users ON users.user_id=job_bill.job_bill_user_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=job_bill.job_bill_project_id ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=job_bill.job_bill_cfop_id ";
                $sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_id=job_bill.job_bill_queue_cost_id ";
                $sql .= "LEFT JOIN queues ON queues.queue_id=job_bill.job_bill_queue_id ";
                $sql .= "WHERE (YEAR(job_bill.job_bill_date)=:year AND month(job_bill.job_bill_date)=:month) ";
		$sql .= "AND job_bill_user_id=:user_id ";
                $sql .= "ORDER BY queues.queue_name ";
                $parameters = array(
                        ':year'=>$this->year,
                        ':month'=>$this->month,
			':user_id'=>$this->user_id
                );
                $this->generic_results = $this->db->query($sql,$parameters);


	}

}

?>
