<?php

class project {

	////////////////Private Variables//////////

	private $db; //mysql database object
	private $owner;
	private $id;
	private $name;
	private $ldap_group;
	private $description;
	private $bill_project;
	private $cfop;
	private $cfop_activity;
	private $enabled;
	private $default;
	private $time_created;
	private $cfop_id;
	////////////////Public Functions///////////

	public function __construct($db,$project_id = 0,$project_name = "") {
		$this->db = $db;
		if ($project_id != 0) {
			$this->load_by_id($project_id);
		}
		elseif (($project_id == 0) && ($project_name != "")) {
			$this->load_by_name($project_name);

		}

	}
	public function __destruct() {
	}

	public function create($name,$ldap_group,$description,$default,$bill_project,$owner_id,$cfop = "",$activity = "",$hide_cfop = 0,$ldap) {
		$error = false;
		$message = "";
		$activity = strtoupper($activity);
		if (!$this->verify_project_name($name)) {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please enter a valid project name.</div>";
		}

		if (!$this->verify_ldap_group($ldap,$ldap_group)) {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please enter a valid LDAP group.</div>";
		}
		if ($description == "") {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please enter a project description.</div>";
		}
		if ($owner_id == "") {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please enter a project owner.</div>";
		}
		if (!$this->verify_cfop($cfop) && $bill_project) {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please enter valid CFOP.</div>";
		}

		if (!$this->verify_activity_code($activity) && $bill_project) {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please enter a valid activity code.</div>";
		}

		if ($error) {
			return array('RESULT'=>false,
					'MESSAGE'=>$message);
		}
		else {
			$project_array = array('project_owner'=>$owner_id,
					'project_name'=>$name,
					'project_ldap_group'=>$ldap_group,
					'project_description'=>$description,
					'project_default'=>$default
				);
			$this->id = $this->db->build_insert("projects",$project_array);
			$this->set_cfop($bill_project,$cfop,$activity,$hide_cfop);
			return array('RESULT'=>true,
					'MESSAGE'=>"<div class='alert alert-success'>Project successfully created.</div>",
					'project_id'=>$this->id);
		}

	}

	public function edit($ldap_group,$description,$bill_project,$owner_id,$cfop = "",$activity = "",$hide_cfop = 0) {
		if (($cfop != $this->get_cfop()) || ($activity != $this->get_activity_code()) ||
				($bill_project != $this->get_bill_project())) {

	                $error = false;
			$message = "";
	                if (!$this->verify_cfop($cfop) && ($bill_project)) {
        	                $error = true;
                	        $message = "<div class='alert alert-danger'>Please verify CFOP</div>";

	                }	
        	        if (!$this->verify_activity_code($activity) && ($bill_project)) {
                	        $error = true;
                        	$message .= "<div class='alert alert-danger'>Please verify activity code</div>";
	                }
			if (!$error) {
				$result = $this->set_cfop($bill_project,$cfop,$activity,$hide_cfop);
				return array('RESULT'=>true,
					'MESSAGE'=>"<div class='alert alert-success'>Project successfully updated.</div>",
					'cfop_id'=>$result);
			}
			else {
				return array('RESULT'=>false,
					'MESSAGE'=>$message);
	
			}
		}

		else {
			$error = false;
			$message = "";
                	if (!$this->verify_ldap_group($ldap_group)) {
                        	$error = true;
	                        $message .= "<div class='alert alert-danger'>Please enter a valid LDAP group.</div>";
        	        }
                	if ($description == "") {
                        	$error = true;
	                        $message .= "<div class='alert alert-danger'>Please enter a project description.</div>";
        	        }
                	if ($owner_id == "") {
                        	$error = true;
	                        $message .= "<div class='alert alert-danger'>Please enter a project owner.</div>";
        	        }
			if (!$error) {
				$sql = "UPDATE projects set project_ldap_group='" . $ldap_group . "', ";
				$sql .= "project_description='" . $description . "',project_owner='" . $owner_id . "' ";
				$sql .= "WHERE project_id='" . $this->get_project_id() . "'";
				$this->db->non_select_query($sql);
				$this->get_project();
				return array('RESULT'=>true,
					'MESSAGE'=>"<div class='alert alert-success'>Project successfully updated.</div>",
					'project_id'=>$this->get_project_id());
			}
			else {
				return array('RESULT'=>false,
					'MESSAGE'=>$message);
			}
		}

	}

	public function get_cfop_id_by_date($inDate) {
		$sql = "SELECT cfop_id FROM cfops ";
		$sql .= "WHERE cfop_project_id='" . $this->get_project_id() . "' ";
		$sql .= "AND cfop_time_created<'" . $inDate . "' ";
		$sql .= "ORDER BY cfop_time_created DESC ";
		$sql .= "LIMIT 1";
		$result = $this->db->query($sql);
		if (count($result)) {
			return $result[0]['cfop_id'];
		}
		return false;

	}
	public function get_project_id() {
		return $this->id;
	}
	public function get_cfop_id() {
		return $this->cfop_id;

	}
	public function get_name() {
		return $this->name;
	}
	public function get_ldap_group() {
		return $this->ldap_group;
	}
	public function get_description() {
		return $this->description;
	}
	public function get_default() {
		return $this->default;
	}
	public function get_bill_project() {
                return $this->bill_project;
        }

	public function get_cfop() {
		return $this->cfop;
	}
	public function get_cfop_college() {
		return substr($this->get_cfop(),0,1);
	}
	public function get_cfop_fund() {
		return substr($this->get_cfop(),2,6);
	}
	public function get_cfop_organization() {
		return substr($this->get_cfop(),9,6);
	}
	public function get_cfop_program() {
		return substr($this->get_cfop(),16,6);
	}
	public function get_activity_code() {
		return $this->cfop_activity;
	}
	public function get_time_created() {
		return $this->time_created;
	}
	public function get_owner() {
		return $this->owner;
	}

	public function get_enabled() {
		return $this->enabled;
	}

	public function get_group_members($ldap) {
		return $ldap->get_group_members($this->get_ldap_group());

	}

	public function is_member($ldap,$username) {
		$group_members = $this->get_group_members($ldap);
		foreach ($group_members as $member) {
			if ($member['memberuid'] == $username) {
				return true;
			}
				
		}
		return false;
	}
	public function set_cfop($bill_project,$cfop,$activity,$hide_cfop = 0) {
		$sql = "UPDATE cfops SET cfop_active='0' ";
		$sql .= "WHERE cfop_project_id='" . $this->get_project_id() . "' ";
		$this->db->non_select_query($sql);
		$active = 1;
		$cfop_billtype = 'no_bill';
		if (!$bill_project) {
			$cfop = "";
			$activity = "";
			$hide_cfop = 0;
			$cfop_billtype = "cfop";
		}
		$insert_array = array('cfop_project_id'=>$this->get_project_id(),
				'cfop_billtype'=>$cfop_billtype,
				'cfop_value'=>$cfop,
				'cfop_activity'=>$activity,
				'cfop_active'=>$active,
				'cfop_restricted'=>$hide_cfop);
		return $this->db->build_insert("cfops",$insert_array);



	}

	public function enable() {
		$sql = "UPDATE projects SET project_enabled='1' ";
		$sql .= "WHERE project_id='" . $this->get_project_id() . "' LIMIT 1";
		$result = $this->db->non_select_query($sql);
		$this->enabled = 1;
		return $result;
	}
	public function disable() {
		$sql = "UPDATE projects SET project_enabled='0' ";
		$sql .= "WHERE project_id='" . $this->get_project_id() . "' LIMIT 1";
		$result = $this->db->non_select_query($sql);
		$this->enabled = 0;
		return $result;
	}

	public static function verify_cfop($cfop) {
		if (preg_match('^[1-9]{1}-[0-9]{6}-[0-9]{6}-[0-9]{6}$^',$cfop)) {
			return true;
		}
		return false;
	}

	public static function verify_activity_code($activity) {
		if ((strlen($activity) == 0) || (preg_match('^[a-zA-Z0-9]^',$activity)
				&& (strlen($activity) <= 6))) {
			return true;
		}
		return false;
	}

	public static function verify_project_name($name) {
		if (!($name == "") && (preg_match('/^[-_a-z0-9]+$/',$name))) {
			return true;
		}
		return false;

	}
	public function verify_ldap_group($ldap,$ldap_group) {

		if (!($ldap_group == "") && ($ldap->get_group_exists($ldap_group))) {
			return true;
		}
		return false;

	}

	public function get_all_cfops() {
		$sql = "SELECT * FROM cfops ";
		$sql .= "WHERE cfop_project_id='" . $this->get_project_id() . "' ";
		$sql .= "ORDER BY cfop_time_created DESC";
		return $this->db->query($sql);


	}
	///////////////Private Functions/////////////

	private function get_project($project_id) {

		$sql = "SELECT projects.*,cfops.*, users.user_name as owner ";
		$sql .= "FROM projects ";
		$sql .= "LEFT JOIN users ON users.user_id=projects.project_owner ";
		$sql .= "LEFT JOIN cfops ON cfops.cfop_project_id=projects.project_id ";
		$sql .= "WHERE project_id='" . $project_id . "' ";
		$sql .= "AND cfops.cfop_active='1' LIMIT 1";	
		$result = $this->db->query($sql);
		if ($result) {
			$this->id = $result[0]['project_id'];
			$this->name = $result[0]['project_name'];
			$this->description = $result[0]['project_description'];
			$this->ldap_group = $result[0]['project_ldap_group'];
			$this->bill_project = $result[0]['cfop_bill'];
			$this->cfop = $result[0]['cfop_value'];
			$this->cfop_activity = $result[0]['cfop_activity'];
			$this->time_created = $result[0]['cfop_time_created'];
			$this->enabled = $result[0]['project_enabled'];
			$this->default = $result[0]['project_default'];
			$this->owner = $result[0]['owner'];
			$this->cfop_id = $result[0]['cfop_id'];
		}
		else { return false;
		}
	}

	private function load_by_id($project_id) {
		$this->get_project($project_id);

	}
	private function load_by_name($project_name) {
		$sql = "SELECT project_id FROM projects ";
		$sql .= "WHERE project_name = '" . $project_name . "' LIMIT 1";
		$result = $this->db->query($sql);
		if (isset($result[0]['project_id'])) {
			$this->get_project($result[0]['project_id']);
		}
	}

}
