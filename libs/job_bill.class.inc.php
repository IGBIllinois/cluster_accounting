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
		$parameters = array(':project_id'=>$project_id,
			':month'=>$month,
			':year'=>$year
		);
                $result = $db->query($sql,$parameters);
                return $result;
	}


	public static function add_job_bill($db,$job_info) {
		$sql = "SELECT count(1) as count FROM job_bill ";
		$sql .= "WHERE job_bill_user_id=:user_id "; 
		$sql .= "AND job_bill_project_id=:project_id ";
		$sql .= "AND job_bill_cfop_id=:cfop_id ";
		$sql .= "AND job_bill_queue_id=:queue_id ";
		$sql .= "AND job_bill_date=:date ";
		$sql .= "LIMIT 1";
		$parameters = array(':user_id'=>$job_info['user_id'],
                        ':project_id'=>$job_info['project_id'],
                        ':cfop_id'=>$job_info['cfop_id'],
                        ':queue_id'=>$job_info['queue_id'],
                        ':date'=>$job_info['date'],
                );
		$check_exists = $db->query($sql,$parameters);
		$result = true;
		if ($check_exists[0]['count']) {
			$result = false;
			$message = "Job Bill: Job Bill already calculated";
		}
		else {
			$insert_array = array(
				'job_bill_user_id'=>$job_info['user_id'],
				'job_bill_project_id'=>$job_info['project_id'],
				'job_bill_cfop_id'=>$job_info['cfop_id'],
				'job_bill_queue_id'=>$job_info['queue_id'],
				'job_bill_queue_cost_id'=>$job_info['queue_cost_id'],
				'job_bill_date'=>$job_info['date'],
				'job_bill_num_jobs'=>$job_info['num_jobs'],
				'job_bill_total_cost'=>$job_info['total_cost'],
				'job_bill_billed_cost'=>$job_info['billed_cost']
			);
			$insert_id = $db->build_insert('job_bill',$insert_array);
			$message = "Job Bill: Job Bill successfully added";
		}
		return array('RESULT'=>$result,'MESSAGE'=>$message);
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
