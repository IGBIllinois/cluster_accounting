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
		$sql = "SELECT users.user_id, users.user_name, ";
                $sql .= "projects.project_id, ";
                $sql .= "cfops.cfop_id, ";
                $sql .= "queues.queue_id, ";
                $sql .= "queue_cost.queue_cost_id, ";
                $sql .= "COUNT(1) as num_jobs, ";
                $sql .= "ROUND(SUM(jobs.job_total_cost),2) as total_cost, ";
                $sql .= "ROUND(SUM(jobs.job_billed_cost),2) as billed_cost, ";
		$sql .= ":year as year, ";
		$sql .= ":month as month ";
                $sql .= "FROM jobs ";
                $sql .= "LEFT JOIN users ON users.user_id=jobs.job_user_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_project_id=projects.project_id ";
                $sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_id=jobs.job_queue_cost_id ";
                $sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
                $sql .= "WHERE (YEAR(jobs.job_end_time)=:year AND month(jobs.job_end_time)=:month) ";
                $sql .= "GROUP BY ";
                $sql .= "queue_cost.queue_cost_id, ";
                $sql .= "jobs.job_project_id, ";
                $sql .= "jobs.job_queue_id, ";
                $sql .= "users.user_name ";
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

	public static function get_jobs_fbs_bill($db,$month,$year) {
                $sql = "SELECT 'IGB' as 'AreaCode','CNRG' as 'FacilityCode', ";
                $sql .= "'' as 'LabCode', IF(users.user_supervisor <> 0,CONCAT(supervisors.user_lastname,', ',supervisors.user_firstname),CONCAT(users.user_lastname,', ',users.user_firstname)) as 'LabName',  ";
                $sql .= "CONCAT(users.user_firstname,users.user_lastname) as 'RequestedBy', ";
                $sql .= "users.user_name as 'NAME', CONCAT(cfops.cfop_value,IF(cfops.cfop_activity <> '','-',''),cfops.cfop_activity) as 'CFOP', ";
                $sql .= "'BIOCLUSTER' as 'SKU_Code', CONCAT(:month,'-',:year) as 'UsageDate', ";
                $sql .= "'1.000' as 'Quantity', ROUND(SUM(job_bill.job_bill_billed_cost),2) as 'UnitPriceOverride', ";
                $sql .= "CONCAT('Biocluster Jobs - ',users.user_name) as 'PrintableComments', ";
                $sql .= "'' as 'UsageRef', '' as 'OrderRef', '' as 'PO_Ref','' as 'PayAlias', ";
                $sql .= "'' as 'bNonBillable','' as 'NonPrintableComments' ";
                $sql .= "FROM job_bill ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=job_bill.job_bill_cfop_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=job_bill.job_bill_project_id ";
                $sql .= "LEFT JOIN users ON users.user_id=projects.project_owner ";
                $sql .= "LEFT JOIN users AS supervisors ON supervisors.user_id=users.user_supervisor ";
                $sql .= "WHERE YEAR(job_bill.job_bill_date)=:year ";
                $sql .= "AND MONTH(job_bill.job_bill_date)=:month ";
                $sql .= "AND cfops.cfop_billtype=:billtype ";
		$sql .= "GROUP BY job_bill.job_bill_user_id ";
                $sql .= "ORDER BY LabName ASC";
                $parameters = array(
                        ':month'=>$month,
                        ':year'=>$year,
                        ':billtype'=>project::BILLTYPE_CFOP
                );
                $report = $db->query($sql,$parameters);

		/**this is currently breaking the report and according to fbs, we dont need to worry about doing this - this comment make labcodes blank
		$fbs_customers = functions::get_fbs_labcodes();
		foreach ($report as &$record) {
			for ($i=0; $i<count($fbs_customers); $i++) {
				if (trim($record['LabName']) == trim($fbs_customers[$i]['CustomerDirectoryName'])) {
					$record['LabCode'] = $fbs_customers[$i]['CustomerCode'];
					break;
				}

			}

		}
		*/
		return $report;
		
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
                $sql .= "LEFT JOIN cfops ON cfops.project_id=projects.project_id ";
                $sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_id=jobs.job_queue_cost_id ";
                $sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
                $sql .= "WHERE (YEAR(jobs.job_end_time)=:year AND month(jobs.job_end_time)=:month) ";
                $sql .= "AND cfops.cfop_billtype=:billtype ";
                $sql .= "GROUP BY ";
                $sql .= "queue_cost.queue_cost_id, ";
                $sql .= "jobs.job_project_id, ";
                $sql .= "jobs.job_queue_id, ";
                $sql .= "users.user_name ";
                $sql .= "HAVING ROUND(SUM(jobs.job_billed_cost),2) > 0.00 ";
                $sql .= "ORDER BY `NAME` ASC";
		$parameters = array(
			':month'=>$month,
			':year'=>$year,
			':billtype'=>project::BILLTYPE_CUSTOM
		);
                $job_result = $db->query($sql,$parameters);
		if (!count($job_result)) {
			$job_result[0] = array('DATE'=>"",
				'NAME'=>"",
				'DESCRIPTION'=>"NO CUSTOM JOB BILLINGS",
				'COST'=>"",
				'PROJECT'=>""
			);
		}
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

	public static function get_running_jobs($db,$user_id = 0,$start = 0 ,$count = 0) {
                $sql = "SELECT IF(ISNULL(running_jobs.job_number_array),running_jobs.job_number, ";
                $sql .= "CONCAT(running_jobs.job_number,'[',running_jobs.job_number_array,']')) as job_number, ";
                $sql .= "running_jobs.job_name as job_name, ";
		$sql .= "running_jobs.job_user as username, ";
                $sql .= "ROUND(running_jobs.job_estimated_cost,2) as current_cost, ";
                $sql .= "running_jobs.job_state as state, ";
                $sql .= "queues.queue_name as queue, ";
                $sql .= "projects.project_name as project, ";
                $sql .= "running_jobs.job_submission_time as submission_time, ";
                $sql .= "running_jobs.job_start_time as start_time, ";
                $sql .= "SEC_TO_TIME(running_jobs.job_ru_wallclock) as elapsed_time, running_jobs.job_cpu_time as cpu_time, ";
                $sql .= "round(running_jobs.job_reserved_mem / 1073741824,2) as mem_reserved, ";
                $sql .= "running_jobs.job_slots as cpus, ";
                $sql .= "running_jobs.job_gpu as gpus ";
                $sql .= "FROM running_jobs ";
                $sql .= "LEFT JOIN queues ON queues.queue_id=running_jobs.job_queue_id ";
                $sql .= "LEFT JOIN projects ON projects.project_id=running_jobs.job_project_id ";
		$parameters = array();
		if ($user_id) {
                	$sql .= "WHERE running_jobs.job_user_id=:user_id ";
			$parameters[':user_id'] = $user_id;		
		}
		$sql .= "ORDER BY current_cost DESC ";
		if ($count != 0) {
                        $sql .= "LIMIT " . $start . "," . $count;
                }
		$result = $db->query($sql,$parameters);
                return $result;
        }

	public static function get_num_running_jobs($db,$user_id) {
		$result = self::get_running_jobs($db,$user_id);
		return count($result);


	}

}

?>
