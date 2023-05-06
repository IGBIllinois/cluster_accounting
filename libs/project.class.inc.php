<?php

class project {

	////////////////Private Variables//////////

	private $db; //mysql database object
	private $owner;
	private $owner_id;
	private $id;
	private $name;
	private $ldap_group;
	private $description;
	private $cfop_billtype;
	private $custom_bill_description = "";
	private $cfop;
	private $cfop_activity;
	private $enabled;
	private $default;
	private $time_created;
	private $cfop_id;

	public const BILLTYPE_CFOP = "cfop";
        public const BILLTYPE_CUSTOM = "custom";
        public const BILLTYPE_NO_BILL = "no_bill";

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

	public function create($ldap,$name,$ldap_group,$description,$default,$cfop_billtype,$owner_id,$cfop = "",$activity = "",$hide_cfop = 0,$custom_bill_description = "") {
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
		if (!\IGBIllinois\cfop::verify_format($cfop,$activity) && $cfop_billtype==self::BILLTYPE_CFOP) {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please enter valid CFOP.</div>";
		}
		try {
                        $cfop_obj =  new \IGBIllinois\cfop(settings::get_cfop_api_key(),settings::get_debug());
                        $cfop_obj->validate_cfop($cfop,$activity);

                }
                catch (\Exception $e) {
                        $error = true;
                        $message .= "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
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
			$this->set_cfop($cfop_billtype,$cfop,$activity,$hide_cfop,$custom_bill_description);
			return array('RESULT'=>true,
					'MESSAGE'=>"<div class='alert alert-success'>Project successfully created.</div>",
					'project_id'=>$this->id);
		}

	}

	public function edit($ldap,$ldap_group,$description,$cfop_billtype,$owner_id,$cfop = "",$activity = "",$hide_cfop = 0,$custom_bill_description = "") {
		if (($cfop != $this->get_cfop()) || ($activity != $this->get_activity_code()) ||
				($cfop_billtype != $this->get_billtype())) {

	                $error = false;
			$message = "";
	                if (!\IGBIllinois\cfop::verify_format($cfop,$activity) && ($cfop_billtype == self::BILLTYPE_CFOP)) {
        	                $error = true;
                	        $message = "<div class='alert alert-danger'>Please verify CFOP</div>";

	                }	
			try {
				$cfop_obj =  new \IGBIllinois\cfop(settings::get_cfop_api_key(),settings::get_debug());
				$cfop_obj->validate_cfop($cfop,$activity);

			}
			catch (\Exception $e) {
				$error = true;
				$message .= "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
			}

			if (!$error) {
				$result = $this->set_cfop($cfop_billtype,$cfop,$activity,$hide_cfop,$custom_bill_description);
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
			if (!$error) {
				if ($this->get_default()) {
					$owner_id = $this->get_owner_id();
				}
				$sql = "UPDATE projects set project_ldap_group=:ldap_group, ";
				$sql .= "project_description=:description,project_owner=:owner_id ";
				$sql .= "WHERE project_id=:project_id";
				$parameters = array(
					':ldap_group'=>$ldap_group,
					':description'=>$description,
					':owner_id'=>$owner_id,
					':project_id'=>$this->get_project_id()
				);
				$this->db->non_select_query($sql,$parameters);
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
		$sql .= "WHERE cfop_project_id=:project_id ";
		$sql .= "AND cfop_time_created<:time_created ";
		$sql .= "ORDER BY cfop_time_created DESC ";
		$sql .= "LIMIT 1";
		$parameters = array(
			':project_id'=>$this->get_project_id(),
			':time_created'=>$inDate
		);
		$result = $this->db->query($sql,$parameters);
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
	public function get_billtype() {
                return $this->cfop_billtype;
        }

	public function get_custom_bill_description() {
		return $this->custom_bill_description;
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

	public function get_owner_id() {
		return $this->owner_id;
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
	public function set_cfop($cfop_billtype,$cfop,$activity,$hide_cfop = 0,$custom_bill_description = "") {
		$sql = "UPDATE cfops SET cfop_active='0' ";
		$sql .= "WHERE cfop_project_id=:project_id ";
		$parameters = array(
			':project_id'=>$this->get_project_id()
		);
		$this->db->non_select_query($sql,$parameters);
		$active = 1;

		switch ($cfop_billtype) {
			case self::BILLTYPE_CFOP:
				$custom_bill_description = "";
				break;
			case self::BILLTYPE_CUSTOM:
				$cfop = "";
				$activity = "";
				$hide_cfop = 0;
				break;
			case self::BILLTYPE_NO_BILL:
				$cfop = "";
				$activity = "";
				$hide_cfop = 0;
				$custom_bill_description = "";
				break;


		}
		
		$insert_array = array('cfop_project_id'=>$this->get_project_id(),
				'cfop_billtype'=>$cfop_billtype,
				'cfop_value'=>$cfop,
				'cfop_activity'=>$activity,
				'cfop_active'=>$active,
				'cfop_restricted'=>$hide_cfop,
				'cfop_custom_description'=>$custom_bill_description
				);
		return $this->db->build_insert("cfops",$insert_array);



	}

	public function enable() {
		$sql = "UPDATE projects SET project_enabled='1' ";
		$sql .= "WHERE project_id=:project_id LIMIT 1";
		$parameters = array(
                        ':project_id'=>$this->get_project_id()
                );
		print_r($parameters);
		$result = $this->db->non_select_query($sql,$parameters);
		$this->enabled = 1;
		return $result;
	}
	public function disable() {
		$cfop_result = $this->disable_all_cfops();
		$sql = "UPDATE projects SET project_enabled='0' ";
		$sql .= "WHERE project_id=:project_id LIMIT 1";
		$parameters = array(
                        ':project_id'=>$this->get_project_id()
                );
		$result = $this->db->non_select_query($sql,$parameters);
		$this->enabled = 0;
		return $result;
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
		$sql .= "WHERE cfop_project_id=:project_id ";
		$sql .= "ORDER BY cfop_time_created DESC";
		$parameters = array(
                        ':project_id'=>$this->get_project_id()
                );
		return $this->db->query($sql,$parameters);


	}

	public function get_directories() {
		$sql = "SELECT * FROM data_dir ";
		$sql .= "WHERE data_dir_project_id=:project_id ";
		$sql .= "AND data_dir_enabled=1 ";
		$sql .= "ORDER BY data_dir_path ASC ";
		$parameters = array(
			':project_id'=>$this->get_project_id()
		);
		return $this->db->query($sql,$parameters);

	}
	///////////////Private Functions/////////////

	private function get_project($project_id) {

		$sql = "SELECT projects.*,cfops.*, users.user_name as owner ";
		$sql .= "FROM projects ";
		$sql .= "LEFT JOIN users ON users.user_id=projects.project_owner ";
		$sql .= "LEFT JOIN cfops ON cfops.cfop_project_id=projects.project_id ";
		$sql .= "WHERE project_id=:project_id ";
		$sql .= "AND cfops.cfop_active='1' LIMIT 1";
		$parameters = array(
                        ':project_id'=>$project_id
                );
		$result = $this->db->query($sql,$parameters);
		if ($result) {
			$this->id = $result[0]['project_id'];
			$this->name = $result[0]['project_name'];
			$this->description = $result[0]['project_description'];
			$this->ldap_group = $result[0]['project_ldap_group'];
			$this->cfop_billtype = $result[0]['cfop_billtype'];
			$this->custom_bill_description = $result[0]['cfop_custom_description'];
			$this->cfop = $result[0]['cfop_value'];
			$this->cfop_activity = $result[0]['cfop_activity'];
			$this->time_created = $result[0]['cfop_time_created'];
			$this->enabled = $result[0]['project_enabled'];
			$this->default = $result[0]['project_default'];
			$this->owner_id = $result[0]['project_owner'];
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
		$sql .= "WHERE project_name=:project_name LIMIT 1";
		$parameters = array(
			':project_name'=>$project_name
		);
		$result = $this->db->query($sql,$parameters);
		if (isset($result[0]['project_id'])) {
			$this->get_project($result[0]['project_id']);
		}
	}

	private function disable_all_cfops() {
		$sql = "UPDATE cfops SET cfop_active=:active ";
		$sql .= "WHERE cfop_project_id=:project_id ";
		$parameters = array(
			':active'=>0,
			':project_id'=>$this->get_project_id()
		);
		$result = $this->db->non_select_query($sql,$parameters);
		return $result;

	}
}

?>
