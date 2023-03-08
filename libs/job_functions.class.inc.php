<?php

class job_functions {

	private const COMPLETED_EXIT_STATUS = "0:0";

	public static function get_jobs_bill($db,$month,$year) {
	
		$sql = "SELECT users.user_name as 'Username', ";
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
		$sql .= "ORDER BY users.user_name,queues.queue_name ";
		$parameters = array(
			':year'=>$year,
			':month'=>$month
		);
	        $result = $db->query($sql,$parameters);
        	return $result;

	}

	public static function get_all_jobs_by_month($db,$month,$year) {
		$sql = "SELECT users.user_id, ";
                $sql .= "projects.project_id, ";
                $sql .= "cfops.cfop_id, ";
                $sql .= "queues.queue_id, ";
                $sql .= "queue_cost.queue_cost_id, ";
                $sql .= "COUNT(1) as num_jobs, ";
                $sql .= "ROUND(SUM(jobs.job_total_cost),2) as total_cost, ";
                $sql .= "ROUND(SUM(jobs.job_billed_cost),2) as billed_cost ";
                $sql .= "FROM jobs ";
                $sql .= "LEFT JOIN users ON users.user_id=jobs.job_user_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=jobs.job_cfop_id ";
                $sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_id=jobs.job_queue_cost_id ";
                $sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
                $sql .= "WHERE (YEAR(jobs.job_end_time)=:year AND month(jobs.job_end_time)=:month) ";
                $sql .= "GROUP BY ";
                $sql .= "queue_cost.queue_cost_id, ";
                $sql .= "jobs.job_cfop_id, ";
                $sql .= "jobs.job_project_id, ";
                $sql .= "jobs.job_queue_id, ";
                $sql .= "users.user_name ";
<<<<<<< HEAD
		$sql .= "HAVING ROUND(SUM(jobs.job_billed_cost),2) > 0.00 ";
                $sql .= "ORDER BY `CFOP` ASC, `ACTIVITY CODE` ASC ";
                $job_result = $db->query($sql);
=======
                $sql .= "ORDER BY users.user_name ";
		$parameters = array(':year'=>$year,
				':month'=>$month
			);
		try {
	                $result = $db->query($sql,$parameters);
		}
		catch(\PDOException $e) {
			echo $e->getMessage();
		}
		return $result;

        }
        public static function get_jobs_boa_bill($db,$month,$year) {


                $sql = "SELECT '' as 'DATE', ";
		$sql .= "users.user_name as 'NAME', ";
                $sql .= "cfops.cfop_value as 'CFOP', ";
                $sql .= "cfops.cfop_activity as 'ACTIVITY CODE', ";
                $sql .= "ROUND(job_bill.job_bill_billed_cost,2) as 'COST', ";
		$sql .= "CONCAT('Biocluster Jobs - ',projects.project_name) as 'DESCRIPTION' ";
                $sql .= "FROM job_bill ";
		$sql .= "LEFT JOIN users ON users.user_id=job_bill.job_bill_user_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=job_bill.job_bill_project_id ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=job_bill.job_bill_cfop_id ";
                $sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_id=job_bill.job_bill_queue_cost_id ";
                $sql .= "LEFT JOIN queues ON queues.queue_id=job_bill.job_bill_queue_id ";
                $sql .= "WHERE (YEAR(job_bill.job_bill_date)=:year AND month(job_bill.job_bill_date)=:month) ";
		$sql .= "AND cfops.cfop_billtype=:billtype ";
		$sql .= "HAVING ROUND(COST,2) > 0.00 ";
		$sql .= "ORDER BY `CFOP` ASC, `ACTIVITY CODE` ASC ";
		$parameters = array(
			':year'=>$year,
			':month'=>$month,
			':billtype'=>project::BILLTYPE_CFOP
		);
                $job_result = $db->query($sql,$parameters);
>>>>>>> devel

		$total_bill = 0;
		foreach ($job_result as $values) {
			$total_bill += $values['COST'];
		}
		$first_row = array(array('DATE'=>$month . "/" . $year,
			'NAME'=>'IGB Biocluster Jobs',
			'CFOP'=>settings::get_boa_cfop(),
			'ACTIVITY CODE'=>'',
			'COST'=>"-" . $total_bill,
			'DESCRIPTION'=>'',
			));

		return array_merge($first_row,$job_result);
		

        }

        public static function get_jobs_custom_bill($db,$month,$year) {


                $sql = "SELECT '' as 'DATE', ";
                $sql .= "users.user_name as 'NAME', ";
                $sql .= "cfops.cfop_custom_description as 'DESCRIPTION', ";
                $sql .= "ROUND(SUM(jobs.job_billed_cost),2) as 'COST', ";
                $sql .= "projects.project_name as 'PROJECT' ";
                $sql .= "FROM jobs ";
                $sql .= "LEFT JOIN users ON users.user_id=jobs.job_user_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=jobs.job_cfop_id ";
                $sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_id=jobs.job_queue_cost_id ";
                $sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
                $sql .= "WHERE (YEAR(jobs.job_end_time)=:year AND month(jobs.job_end_time)=:month) ";
                $sql .= "AND cfops.cfop_bill='1' ";
		$sql .= "AND cfops.custom_billi='1' ";
                $sql .= "GROUP BY ";
                $sql .= "queue_cost.queue_cost_id, ";
                $sql .= "jobs.job_cfop_id, ";
                $sql .= "jobs.job_project_id, ";
                $sql .= "jobs.job_queue_id, ";
                $sql .= "users.user_name ";
                $sql .= "HAVING ROUND(SUM(jobs.job_billed_cost),2) > 0.00 ";
                $sql .= "ORDER BY `NAME` ASC";
		$paramters = array(
			':month'=>$month,
			':year'=>$year
		);
                $job_result = $db->query($sql,$parameters);


                return $job_result;


        }

	public static function get_jobs($db,$user_id = 0,$search = "",$completed = -1,
					$start_date=0,$end_date=0,$start = 0,$count = 0) {

	        $search = strtolower(trim(rtrim($search)));
        	$where_sql = array();
		if ($user_id != 0) {
	        	array_push($where_sql,"user_id='" .$user_id . "'");
		}
        	if (($start_date != 0) && ($end_date != 0)) {
                	array_push($where_sql, "DATE(end_time) BETWEEN '" . $start_date . "' AND '" . $end_date . "' ");
	        }	

		if ($completed == 0) {
			array_push($where_sql, "exit_status<>'" . self::COMPLETED_EXIT_STATUS . "' ");

		}
		elseif ($completed == 1) {
			array_push($where_sql, "exit_status='" . self::COMPLETED_EXIT_STATUS . "' ");
		}
        	$sql = "SELECT id as id, ";
		$sql .= "job_number_full, ";
		$sql .= "job_number, job_number_array, ";
		$sql .= "job_name, start_time as start_time,";
	        $sql .= "end_time as end_time, ROUND(total_cost,2) as total_cost, ";
	        $sql .= "ROUND(billed_cost,2) as billed_cost, ";
        	$sql .= "SEC_TO_TIME(wallclock_time) as elapsed_time, ";
	        $sql .= "username as username, ";
        	$sql .= "project_name as project, queue_name as queue, ";
	        $sql .= "cfop as cfop, activity_code as activity, ";
		$sql .= "exit_status as exit_status ";
        	$sql .= "FROM job_info ";

	        if ($search != "" ) {
        	        $terms = explode(" ",$search);
                	foreach ($terms as $term) {
	                        $search_sql = "(job_name LIKE '%" . $term . "%' OR ";
        	                $search_sql .= "queue_name LIKE '%" . $term . "%' OR ";
                	        $search_sql .= "cfop LIKE '%" . $term . "%' OR ";
                        	$search_sql .= "activity_code LIKE '%" . $term . "%' OR ";
	                        $search_sql .= "project_name LIKE '%" . $term . "%' OR ";
        	                $search_sql .= "job_number LIKE '%" . $term . "%' OR ";
				$search_sql .= "exec_hosts LIKE '%" . $term . "%') ";
                	        array_push($where_sql,$search_sql);
	                }

        	}
	        $num_where = count($where_sql);
        	if ($num_where) {
	                $sql .= "WHERE ";
                	$i = 0;
        	        foreach ($where_sql as $where) {
                	        $sql .= $where;
	                        if ($i<$num_where-1) {
        	                        $sql .= "AND ";
                	        }
                        	$i++;
	                }

        	}
	        $sql .= " ORDER BY job_number,job_number_array ASC ";

                if ($count != 0) {
                        $sql .= "LIMIT " . $start . "," . $count;
                }
        	$result = $db->query($sql);
	        return $result;

}


        public static function get_num_jobs($db,$user_id = 0,$search = "",$completed = -1,$start_date=0,$end_date=0) {

                $search = strtolower(trim(rtrim($search)));
                $where_sql = array();
                if ($user_id != 0) {
                        array_push($where_sql,"user_id='" .$user_id . "'");
                }
                if (($start_date != 0) && ($end_date != 0)) {
                        array_push($where_sql, "DATE(end_time) BETWEEN '" . $start_date . "' AND '" . $end_date . "' ");
                }
		if ($completed == 0) {
                        array_push($where_sql, "exit_status<>'" . self::COMPLETED_EXIT_STATUS . "' ");

                }
                elseif ($completed == 1) {
                        array_push($where_sql, "exit_status='" . self::COMPLETED_EXIT_STATUS . "' ");
                }

                $sql = "SELECT count(1) as count ";
                $sql .= "FROM job_info ";

                if ($search != "" ) {
                        $terms = explode(" ",$search);
                        foreach ($terms as $term) {
                                $search_sql = "(job_name LIKE '%" . $term . "%' OR ";
                                $search_sql .= "queue_name LIKE '%" . $term . "%' OR ";
                                $search_sql .= "cfop LIKE '%" . $term . "%' OR ";
                                $search_sql .= "activity_code LIKE '%" . $term . "%' OR ";
                                $search_sql .= "project_name LIKE '%" . $term . "%' OR ";
                                $search_sql .= "job_number LIKE '%" . $term . "%') ";
                                array_push($where_sql,$search_sql);
                        }

                }
                $num_where = count($where_sql);
                if ($num_where) {
                        $sql .= "WHERE ";
                        $i = 0;
                        foreach ($where_sql as $where) {
                                $sql .= $where;
                                if ($i<$num_where-1) {
                                        $sql .= "AND ";
                                }
                                $i++;
                        }

                }
                $result = $db->query($sql);
                return $result[0]['count'];


	}

}

?>
