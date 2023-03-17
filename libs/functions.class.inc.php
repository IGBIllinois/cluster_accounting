<?php

class functions {

	private const SECS_IN_DAY = 86400;

	public static function get_queues($db,$selection = 'ALL') {
        	$sql = "SELECT queues.queue_id as queue_id, ";
		$sql .= "queues.queue_name as name, ";
		$sql .= "queues.queue_ldap_group as ldap_group, ";
		$sql .= "queues.queue_description as description, ";
		$sql .= "queues.queue_time_created as time_created, ";
		$sql .= "a.queue_cost_id as cost_id, ";
		$sql .= "a.queue_cost_mem as cost_memory_secs, ";
		$sql .= "a.queue_cost_mem * :secs_in_day as cost_memory_day, ";
		$sql .= "a.queue_cost_cpu as cost_cpu_secs, ";
		$sql .= "a.queue_cost_cpu * :secs_in_day as cost_cpu_day, ";
		$sql .= "a.queue_cost_gpu as cost_gpu_secs, ";
		$sql .= "a.queue_cost_gpu * :secs_in_day as cost_gpu_day ";
	        $sql .= "FROM queues ";
		$sql .= "LEFT JOIN (SELECT * FROM queue_cost n ";
		$sql .= "WHERE queue_cost_time_created=(SELECT MAX(queue_cost_time_created) ";
		$sql .= "FROM queue_cost WHERE queue_cost_queue_id=n.queue_cost_queue_id)) a ";
		$sql .= "ON queues.queue_id=a.queue_cost_queue_id ";
	        $sql .= "WHERE queue_enabled='1' ";
		if ($selection == 'PUBLIC') {
			$sql .= "AND queue_ldap_group='' ";
		}
		elseif ($selection == 'PRIVATE') {
			$sql .= "AND queue_ldap_group!='' ";
		}
		$parameters = array (
			':secs_in_day'=>self::SECS_IN_DAY
		);
	        $sql .= "ORDER BY queues.queue_name ASC";
        	return $db->query($sql,$parameters);
	}


	public static function get_projects($db,$enabled = 1, $custom = 'ALL', $search = '', $start=0,$count=0) {

		$search = strtolower(trim(rtrim($search)));
		$where_sql = array();
		if ($enabled) {
			array_push($where_sql,"project_enabled='1' ");
		}
		array_push($where_sql,"cfops.cfop_active='1' ");
	
		$sql = "SELECT projects.*,cfops.*, users.user_name as owner ";
		$sql .= "FROM projects ";
		$sql .= "LEFT JOIN users ON users.user_id=projects.project_owner ";
		$sql .= "LEFT JOIN cfops ON cfops.cfop_project_id=projects.project_id ";
		
		if ($custom == 'CUSTOM') {
			array_push($where_sql,"project_default='0' ");
		}
		elseif ($custom == 'DEFAULT') {
			array_push($where_sql,"project_default='1' ");
		}

                if ($search != "" ) {
                        $terms = explode(" ",$search);
                        foreach ($terms as $term) {
                                $search_sql = "(projects.project_name LIKE '%" . $term . "%' OR ";
                                $search_sql .= "projects.project_ldap_group LIKE '%" . $term . "%' OR ";
                                $search_sql .= "projects.project_description LIKE '%" . $term . "%' OR ";
				$search_sql .= "cfops.cfop_value LIKE '%" . $term . "%' OR ";
                                $search_sql .= "cfops.cfop_activity LIKE '%" . $term . "%' OR ";
                                $search_sql .= "users.user_name LIKE '%" . $term . "%') ";
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

		$sql .= "ORDER BY projects.project_name ASC ";
		if ($count != 0) {
			$sql .= "LIMIT " . $start . "," . $count;
		}
		return $db->query($sql);

	}

	public static function get_num_projects($db,$custom = 'ALL',$search = '') {

                $search = strtolower(trim(rtrim($search)));
                $where_sql = array();
                array_push($where_sql,"project_enabled='1' ");
		array_push($where_sql,"cfops.cfop_active='1' ");

		$sql = "SELECT count(1) as count ";
		$sql .= "FROM projects ";
                $sql .= "LEFT JOIN users ON users.user_id=projects.project_owner ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_project_id=projects.project_id ";

		if ($custom == 'CUSTOM') {
			array_push($where_sql,"project_default='0' ");
		}
		elseif ($custom == 'DEFAULT') {
			array_push($where_sql,"project_default='1' ");
		}
		elseif ($custom == 'ALL') {
		}

		if ($search != "" ) {
                        $terms = explode(" ",$search);
                        foreach ($terms as $term) {
                                $search_sql = "(projects.project_name LIKE '%" . $term . "%' OR ";
                                $search_sql .= "projects.project_ldap_group LIKE '%" . $term . "%' OR ";
                                $search_sql .= "projects.project_description LIKE '%" . $term . "%' OR ";
                                $search_sql .= "cfops.cfop_value LIKE '%" . $term . "%' OR ";
                                $search_sql .= "cfops.cfop_activity LIKE '%" . $term . "%' OR ";
                                $search_sql .= "users.user_name LIKE '%" . $term . "%') ";
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

	public static function output_message($messages) {
		$output = "";
		foreach ($messages as $message) {
			if ($message['RESULT']) {
				$output .= "<div class='alert alert-success'>" . $message['MESSAGE'] . "</div>";
			}
			else {
				$output .= "<div class='alert alert-danger'>" . $message['MESSAGE'] . "</div>";
			}
		}
		return $output;

	}

	public static function get_cfop($db,$cfop_id) {
		$sql = "SELECT * FROM cfops ";
		$sql .= "WHERE cfop_id=:cfop_id ";
		$sql .= "LIMIT 1";
		$parameters = array(
			':cfop_id'=>$cfop_id
		);
		return $db->query($sql,$parameters);

	}

	public static function get_all_cfops($db) {
		$sql = "SELECT cfop_value as cfop, ";
		$sql .= "cfop_activity as activity_code ";
		$sql .= "FROM cfops ";
		$sql .= "WHERE cfop_billtype=:billtype ";
		$sql .= "AND cfop_active=1 ";
		$sql .= "GROUP BY cfop_value,cfop_activity ";
		$sql .= "ORDER BY cfop,activity_code ";
		$parameters = array(
			'billtype'=>project::BILLTYPE_CFOP
		);
		return $db->query($sql,$parameters);


	}

	public static function recursive_array_search($needle,$haystack) {
		foreach($haystack as $key=>$value) {
			$current_key=$key;
			if ($needle===$value OR (is_array($value) && self::recursive_array_search($needle,$value) !== false)) {
				return $current_key;
        		}
		}
		return false;
	}
}

?>
