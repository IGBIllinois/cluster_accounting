<?php

class job_bill {

	public static function get_job_summary($db,$project_id,$month,$year) {
                $sql = "SELECT users.user_id, ";
		$sql .= "projects.project_id, ";
		$sql .= "cfops.cfop_id, ";
		$sql .= "queues.queue_id, ";
		$sql .= "queue_cost.queue_cost_id, ";
		$sql .= "COUNT(1) as num_jobs, ";
                $sql .= "ROUND(SUM(jobs.job_total_cost),2) as total_cost, ";
                $sql .= "ROUND(SUM(jobs.job_billed_cost),2) as billed_cost, ";
                $sql .= "cfops.cfop_id ";
                $sql .= "FROM jobs ";
		$sql .= "LEFT JOIN users ON users.user_id=jobs.job_user_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=jobs.job_cfop_id ";
                $sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_id=jobs.job_queue_cost_id ";
                $sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
                $sql .= "WHERE jobs.job_project_id=:project_id AND ";
		$sql .= "YEAR(jobs.job_end_time)=:year AND month(jobs.job_end_time)=:month ";
                $sql .= "GROUP BY ";
                $sql .= "queue_cost.queue_cost_id, ";
                $sql .= "jobs.job_cfop_id, ";
                $sql .= "jobs.job_project_id, ";
                $sql .= "jobs.job_queue_id, ";
		$sql .= "users.user_name ";
		$params = array(':project_id'=>$project_id,
			':month'=>$month,
			':year'=>$year
		);
                $result = $db->query($sql,$params);
                return $result;
	}


	public static function add_job_usage($db,$job_info) {
		$sql = "INSERT INTO job_bill (job_bill_user_id,job_bill_project_id,job_bill_cfop_id, ";
		$sql .= "job_bill_queue_id, job_bill_queue_cost_id, job_bill_date,";
		$sql .= "job_bill_num_jobs,job_bill_total_cost,job_bill_billed_cost) ";
		$sql .= "VALUES(:user_id,:project_id,:cfop_id,:queue_id,:queue_cost_id,:date,:num_jobs,:total_cost,:billed_cost) ";
		$params = array(':user_id'=>$job_info['user_id'],
			':project_id'=>$job_info['project_id'],
			':cfop_id'=>$job_info['cfop_id'],
			':queue_id'=>$job_info['queue_id'],
			':queue_cost_id'=>$job_info['queue_cost_id'],
			':date'=>$job_info['date'],
			':num_jobs'=>$job_info['num_jobs'],
			':total_cost'=>$job_info['total_cost'],
			':billed_cost'=>$job_info['billed_cost']
		);
		$result = $db->insert_query($sql,$params);
		return $result;

	}

	public static function get_minimal_year($db) {
		$sql = "SELECT MIN(YEAR(job_bill_date)) as year ";
		$sql .= "FROM job_bill ";
		$result = $db->query($sql);
		if (count($result)) {
			return $result[0]['year'];
		}
		return date('Y');

	}
}

?>
