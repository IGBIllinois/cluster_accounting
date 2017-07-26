<?php

class job_functions {


	public static function get_jobs_bill($db,$month,$year) {

		
		$sql = "SELECT users.user_name as 'Username', ";
		$sql .= "projects.project_name as 'Project', ";
		$sql .= "queues.queue_name as 'Queue', ";
		$sql .= "queue_cost.queue_cost_cpu as 'Queue CPU Cost (Per Second)', ";
		$sql .= "queue_cost.queue_cost_mem as 'Queue Memory Cost (Per GB)', ";
		$sql .= "ROUND(SUM(jobs.job_total_cost),2) as 'Total Cost', ";
		$sql .= "ROUND(SUM(jobs.job_billed_cost),2) as 'Billed Cost', ";
		$sql .= "cfops.cfop_value as 'CFOP', ";
		$sql .= "cfops.cfop_activity as 'Activity Code' ";
		$sql .= "FROM jobs ";
		$sql .= "LEFT JOIN users ON users.user_id=jobs.job_user_id ";
		$sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
		$sql .= "LEFT JOIN cfops ON cfops.cfop_id=jobs.job_cfop_id ";
		$sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_id=jobs.job_queue_cost_id ";
		$sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
		$sql .= "WHERE (YEAR(jobs.job_end_time)='" . $year . "' AND month(jobs.job_end_time)='" . $month . "') ";
		$sql .= "GROUP BY ";
		$sql .= "queue_cost.queue_cost_id, ";
		$sql .= "jobs.job_cfop_id, ";
		$sql .= "jobs.job_project_id, ";
		$sql .= "jobs.job_queue_id, ";
		$sql .= "users.user_name ";
		$sql .= "ORDER BY users.user_name ";
	        $result = $db->query($sql);
        	return $result;

	}


	public static function get_jobs($db,$user_id = 0,$search = "",
					$start_date=0,$end_date=0,$start = 0,$count = 0) {

	        $search = strtolower(trim(rtrim($search)));
        	$where_sql = array();
		if ($user_id != 0) {
	        	array_push($where_sql,"jobs.job_user_id='" .$user_id . "'");
		}
        	if (($start_date != 0) && ($end_date != 0)) {
                	array_push($where_sql, "DATE(jobs.job_end_time) BETWEEN '" . $start_date . "' AND '" . $end_date . "' ");
	        }	

        	$sql = "SELECT jobs.job_id as id, ";
		$sql .= "IF(ISNULL(jobs.job_number_array),jobs.job_number, ";
		$sql .= "CONCAT(jobs.job_number,'[',jobs.job_number_array,']')) as job_number_full, ";
		$sql .= "jobs.job_number, jobs.job_number_array, ";
		$sql .= "jobs.job_name, jobs.job_start_time as start_time,";
	        $sql .= "jobs.job_end_time as end_time, ROUND(jobs.job_total_cost,2) as total_cost, ";
	        $sql .= "ROUND(jobs.job_billed_cost,2) as billed_cost, ";
        	$sql .= "SEC_TO_TIME(jobs.job_ru_wallclock) as elapsed_time, ";
	        $sql .= "users.user_name as username,users.user_full_name as full_name, ";
        	$sql .= "projects.project_name as project, queues.queue_name as queue, ";
	        $sql .= "cfops.cfop_value as cfop, cfops.cfop_activity as activity ";
        	$sql .= "FROM jobs ";
	        $sql .= "LEFT JOIN users ON users.user_id=jobs.job_user_id ";
        	$sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
	        $sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
        	$sql .= "LEFT JOIN cfops ON cfops.cfop_id=jobs.job_cfop_id ";

	        if ($search != "" ) {
        	        $terms = explode(" ",$search);
                	foreach ($terms as $term) {
	                        $search_sql = "(jobs.job_name LIKE '%" . $term . "%' OR ";
        	                $search_sql .= "queues.queue_name LIKE '%" . $term . "%' OR ";
                	        $search_sql .= "cfops.cfop_value LIKE '%" . $term . "%' OR ";
                        	$search_sql .= "cfops.cfop_activity LIKE '%" . $term . "%' OR ";
	                        $search_sql .= "jobs.job_project LIKE '%" . $term . "%' OR ";
        	                $search_sql .= "jobs.job_number LIKE '%" . $term . "%') ";
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
	        $sql .= " ORDER BY jobs.job_number,jobs.job_number_array ASC ";

                if ($count != 0) {
                        $sql .= "LIMIT " . $start . "," . $count;
                }

        	$result = $db->query($sql);
	        return $result;

}










}







?>
