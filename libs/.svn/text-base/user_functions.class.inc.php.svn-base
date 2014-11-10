<?php

class user_functions {


	public static function get_supervisors($db) {
	        $sql = "SELECT user_name as username,user_full_name as full_name,user_id as id ";
	        $sql .= "FROM users ";
        	$sql .= "WHERE user_enabled='1' AND user_supervisor='0' ORDER BY username ASC";
	        return $db->query($sql);
	}


	//get_users()
	//returns array of users
	function get_users($db,$ldap = "",$search = "") {
        	$search = strtolower(trim(rtrim($search)));
	        $where_sql = array();
	
        	$sql = "SELECT users.* FROM users ";
	        array_push($where_sql,"users.user_enabled='1'");

        	if ($search != "" ) {
                	$terms = explode(" ",$search);
	                foreach ($terms as $term) {
        	                $search_sql = "(LOWER(users.user_name) LIKE '%" . $term . "%' OR ";
                	        $search_sql .= "LOWER(users.user_full_name) LIKE '%" . $term . "%') ";
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
        	$sql .= " ORDER BY users.user_name ASC ";
        	$result = $db->query($sql);
	
	        if ($ldap != "") {
        	        $ldap_all_users = $ldap->get_all_users();
                	foreach ($result as &$user) {
	                        if (in_array($user['user_name'],$ldap_all_users)) {
        	                        $user['user_ldap'] = 1;
	
        	                }
                	        else {
                        	        $user['user_ldap'] = 0;
	                        }
        	        }
	        }
        	return $result;

	}

	public static function get_users_with_cluster_perms($ldap = "",$host_attribute) {
		$search = "host=" . $host_attribute;
		$result = $ldap->search($search,"",array('uid'));
		$users = array();
		foreach ($result as $value) {
			array_push($users,$value['uid'][0]);
			
		}
		return $users;
		
		
	}
	
	public static function get_users_not_in_accounting($db,$ldap = "",$host_attribute) {
		$full_accounting_users = self::get_users($db);
		$accounting_users = array();
		foreach ($full_accounting_users as $user) {
			array_push($accounting_users,$user['user_name']);	
		
		}
		$cluster_users = self::get_users_with_cluster_perms($ldap,$host_attribute);
		$result =  array_diff($cluster_users,$accounting_users);
		asort($result);
		return $result;		
		
	}
	public static function get_num_users($db) {
	        $sql = "SELECT count(1) as count FROM users ";
        	$sql .= "WHERE user_enabled=1";
	        $result = $db->query($sql);
        	return $result[0]['count'];

	}

	public static function get_disabled_users($db) {
        	$sql = "SELECT * FROM users WHERE user_enabled='0' ORDER BY user_name ASC";
	        return $db->query($sql);

	}

}

?>
