<?php
class user {

	////////////////Private Variables//////////

	private $db; //mysql database object
	private $id;
	private $user_name;
	private $firstname;
	private $lastname;
	private $supervisor_name;
	private $supervisor_id;
	private $enabled;
	private $time_created;
	private $ldap;
	private $email;
	private $admin;
	private $default_project_id;
	private $default_data_dir_id;	
	private const USER_BILL_TWIG = "user_bill.html.twig";
	private $ldap_attributes = array(
			'firstname'=>'givenname',
			'lastname'=>'sn',
			'fullname'=>'cn',
			'homedir'=>'homedirectory',
			'username'=>'uid'
		);
	////////////////Public Functions///////////

	public function __construct(&$db,&$ldap,$id = 0,$username = "") {
		$this->db = $db;
		$this->ldap = $ldap;
		if ($id != 0) {
			$this->load_by_id($id);
		}
		elseif (($id == 0) && ($username != "")) {
			$this->load_by_username($username);
			$this->user_name = $username;
		}
	}
	
	public function __destruct() {
	
	}

	public function create($username,$supervisor_id,$admin,$cfop_billtype,$cfop = "",$activity = "",$hide_cfop = 0,$custom_bill_description = "") {
		$username = trim(rtrim($username));
		$error = false;
		//Verify Username
		$message = "";
		if ($username == "") {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please enter a username</div>";
		}
		elseif (preg_match('/[A-Z]/',$username)) {
			$error = true;
			$message .= "<div class='alert alert-danger'>Username can only be lowercase</div>";
		}
		elseif ($this->get_user_exist($username)) {
			$error = true;
			$message .= "<div class='alert alert-danger'>User " . $username . " already exists in database</div>";
		}
		elseif (!$this->ldap->is_ldap_user($username)) {
			$error = true;
			$message .= "<div class='alert alert-danger'>User " . $username . " does not exist in LDAP database.</div>";
		}

		if (is_null($supervisor_id) || !is_numeric($supervisor_id)) {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please select a supervisor.</div>";
		}
		//Verify CFOP/Activty Code
		if (!\IGBIllinois\cfop::verify_format($cfop,$activity) && $cfop_billtype == project::BILLTYPE_CFOP) {
			$error = true;
			$message .= "<div class='alert alert-danger'>Invalid CFOP.</div>";
		}

		try {
			$cfop_obj =  new \IGBIllinois\cfop(settings::get_cfop_api_key(),settings::get_debug());
			$cfop_obj->validate_cfop($cfop,$activity);
			
		}
		catch (\Exception $e) {
			$error = true;
			$message .= "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
		}
		if (($cfop_billtype == project::BILLTYPE_CUSTOM) && ($custom_bill_description == ""))  {
			$error = true;
			$message .= "<div class='alert alert-danger'>Please enter a custom bill description</div>";
		}
		//If Errors, return with error messages
		if ($error) {
			return array('RESULT'=>false,
					'MESSAGE'=>$message);
		}

		//Everything looks good, add user and default user project
		else {
			$ldap_filter = "(" . $this->ldap_attributes['username'] . "=" . $username . ")";
			$attributes = array_values($this->ldap_attributes);
			$ou = settings::get_ldap_base_dn();
			$ldap_result = $this->ldap->search($ldap_filter,$ou,$attributes);
			$firstname = "";
			$lastname = "";
			$home_dir = "";
			if ($ldap_result['count'] == 1) {
				$firstname = $ldap_result[0][$this->ldap_attributes['firstname']][0];
				$lastname = $ldap_result[0][$this->ldap_attributes['lastname']][0];
				$home_dir = $ldap_result[0][$this->ldap_attributes['homedir']][0];
			}
			try {
				$sql = "INSERT INTO users(user_name,user_firstname,user_lastname,user_admin,user_supervisor,user_enabled) ";
				$sql .= "VALUES(:username,:firstname,:lastname,:admin,:supervisor_id,:enabled) ";
				$sql .= "ON DUPLICATE KEY UPDATE user_firstname=:firstname,user_lastname=:lastname,user_admin=:admin,";
				$sql .= "user_supervisor=:supervisor_id,user_enabled=:enabled";
				$parameters = array(':username'=>$username,
					':firstname'=>$firstname,
					':lastname'=>$lastname,
					':admin'=>$admin,
					':supervisor_id'=>$supervisor_id,
					':enabled'=>1
				);
				$user_id = $this->db->insert_query($sql,$parameters);
				$this->load_by_id($user_id);
				$description = "default";
				$default = 1;
				$project = new project($this->db);
				$project->create($this->ldap,$username,$username,$description,$default,$cfop_billtype,$user_id,$cfop,$activity,$hide_cfop,$custom_bill_description);
				$data_dir = new data_dir($this->db);
				$default = 1;
				$data_dir->create($project->get_project_id(),$home_dir,$default);
			}
			catch (\PDOException | \Exception $e) {
				throw new \Exception($e->getMessage());
				return array('RESULT'=>false,
                                        'MESSAGE'=>$e->getMessage()
                                );

			}
			return array('RESULT'=>true,
				'MESSAGE'=>"User " . $username . " succesfully added.",
				'user_id'=>$this->get_user_id());
		}

	}
	public function get_user_id() {
		return $this->id;
	}
	public function get_username() {
		return $this->user_name;
	}
	public function get_email() {
		return $this->email;
	}
	public function get_firstname() {
		return $this->firstname;
	}
	public function get_lastname() {
		return $this->lastname;
	}
	public function get_full_name() {
		return $this->get_firstname() . " " . $this->get_lastname();
	}
	public function get_supervisor_name() {
		return $this->supervisor_name;
	}
	public function get_supervisor_id() {
		return $this->supervisor_id;
	}
	public function get_enabled() {
		return $this->enabled;
	}
	public function get_time_created() {
		return $this->time_created;
	}
	public function get_jobs_summary($month,$year) {

                $sql = "SELECT projects.project_name as 'project', ";
                $sql .= "queues.queue_name as 'queue', ";
                $sql .= "ROUND(job_bill.job_bill_total_cost,2) as 'total_cost', ";
                $sql .= "ROUND(job_bill.job_bill_billed_cost,2) as 'billed_cost', ";
                $sql .= "cfops.cfop_value as 'cfop', ";
                $sql .= "cfops.cfop_activity as 'activity_code', ";
		$sql .= "cfops.cfop_restricted as cfop_restricted ";
                $sql .= "FROM job_bill ";
                $sql .= "LEFT JOIN projects ON projects.project_id=job_bill.job_bill_project_id ";
                $sql .= "LEFT JOIN cfops ON cfops.cfop_id=job_bill.job_bill_cfop_id ";
                $sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_id=job_bill.job_bill_queue_cost_id ";
                $sql .= "LEFT JOIN queues ON queues.queue_id=job_bill.job_bill_queue_id ";
                $sql .= "WHERE (YEAR(job_bill.job_bill_date)=:year AND month(job_bill.job_bill_date)=:month) ";
		$sql .= "AND job_bill_user_id=:user_id ";
                $parameters = array(
			':user_id'=>$this->get_user_id(),
			':year'=>$year,
			':month'=>$month
		);
                $result = $this->db->query($sql,$parameters);

		foreach($result as $key=>$value) {
			if ($value['total_cost'] == 0.00) {
				$result[$key]['total_cost'] = 0.01;
			}
		}
		return $result;
	}
	public function get_jobs_report($start_date,$end_date) {
                $sql = "SELECT IF(ISNULL(jobs.job_number_array),jobs.job_number, ";
                $sql .= "CONCAT(jobs.job_number,'[',jobs.job_number_array,']')) as 'Job Number', ";
		$sql .= "jobs.job_name as 'Job Name', ";
		$sql .= "jobs.job_total_cost as 'Cost', ";
		$sql .= "queues.queue_name as 'Queue', ";
		$sql .= "projects.project_name as 'Project', ";
		$sql .= "jobs.job_submission_time as 'Submission Time', ";
		$sql .= "jobs.job_start_time as 'Start Time', jobs.job_end_time as 'End Time', ";
		$sql .= "jobs.job_ru_wallclock as 'Elapsed Time (Secs)', jobs.job_cpu_time as 'CPU Time (Secs)', ";
		$sql .= "round(jobs.job_reserved_mem / 1073741824,2) as 'Reserved Memory (GB)', ";
		$sql .= "round(jobs.job_used_mem /1073741824,2) as 'Used Memory (GB)', ";
		$sql .= "round(jobs.job_maxvmem / 1073741824,2) as 'Virtual Memory (GB)', ";
		$sql .= "jobs.job_slots as 'CPUs' ";
		$sql .= "FROM jobs ";
		$sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
		$sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
		$sql .= "WHERE DATE(jobs.job_end_time) BETWEEN :start_date AND :end_date ";
		$sql .= "AND jobs.job_user_id=:user_id ";
		$parameters = array(
			':user_id'=> $this->get_user_id(),
                        ':start_date'=> $start_date,
                        ':end_date'=>$end_date
                );
		$result = $this->db->query($sql,$parameters);
		return $result;
	}

	public function get_data_summary($month,$year) {
		$sql = "SELECT data_dir.data_dir_path as directory, ";
		$sql .= "data_cost.data_cost_value as data_cost_value, ";
		$sql .= "projects.project_name as project, ";
		$sql .= "ROUND((data_bill.data_bill_avg_bytes / :terabytes),:data_precision) as terabytes, ";
		$sql .= "ROUND(data_bill.data_bill_total_cost,2) as total_cost, ";
		$sql .= "ROUND(data_bill.data_bill_billed_cost,2) as billed_cost, ";
		$sql .= "cfops.cfop_value as cfop, ";
		$sql .= "cfops.cfop_activity as activity_code, ";
		$sql .= "cfops.cfop_restricted as cfop_restricted ";
		$sql .= "FROM data_bill ";
		$sql .= "LEFT JOIN projects ON projects.project_id=data_bill.data_bill_project_id ";
		$sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_bill.data_bill_data_dir_id ";
		$sql .= "LEFT JOIN cfops ON cfops.cfop_id=data_bill.data_bill_cfop_id ";
		$sql .= "LEFT JOIN data_cost ON data_cost.data_cost_id=data_bill.data_bill_data_cost_id ";
		$sql .= "WHERE projects.project_owner=:owner ";
		$sql .= "AND YEAR(data_bill.data_bill_date)=:year ";
	        $sql .= "AND MONTH(data_bill.data_bill_date)=:month ";
		$parameters = array(
			':owner'=>$this->get_user_id(),
			':month'=>$month,
			':year'=>$year,
			':terabytes'=>data_functions::CONVERT_TERABYTES,
			':data_precision'=>data_functions::DATA_PRECISION
		);
		return $this->db->query($sql,$parameters);
		
	}
	public function default_project() {
		$project = new project($this->db,0,$this->get_username());
		return $project;

	}
	public function default_data_dir() {
		$data_dir = new data_dir($this->db,$this->default_data_dir_id);
		return $data_dir;
	}
	public function get_projects() {
		$sql = "SELECT * FROM projects WHERE project_enabled='1'";
		$all_projects = $this->db->query($sql);
		$ldap_groups = $this->ldap->get_user_groups($this->get_username());
		$user_projects = array();
		foreach ($ldap_groups as $group) {
			foreach ($all_projects as $project) {
				if ($group == $project['project_ldap_group']) {
					array_push($user_projects,$project);
				}

			}

		}
		return $user_projects;

	}

	public function get_owned_projects() {
                $sql = "SELECT * FROM projects ";
                $sql .= "WHERE project_owner=:owner ";
                $sql .= "AND project_enabled='1'";
		$parameters = array (
			':owner'=>$this->get_user_id()
		);
                return $this->db->query($sql,$parameters);
        }

	public function is_project_member($project) {
		$user_projects = $this->get_projects();
		foreach ($user_projects as $user_project) {
                        if ($user_project['project_name'] == $project) {
                                return true;
                        }
                }
		return false;
	}
	public function get_queues() {
		$sql = "SELECT queue_name,queue_ldap_group FROM queues WHERE queue_enabled='1'";
		$all_queues = $this->db->query($sql);
		$ldap_groups = $this->ldap->get_user_groups($this->get_username());
		$user_queues = array();
		foreach ($all_queues as $queue) {
			if ($queue['queue_ldap_group'] === "") {
				array_push($user_queues,$queue['queue_name']);
			}
			else {
				foreach ($ldap_groups as $group) {
					if ($group == $queue['queue_ldap_group']) {
						array_push($user_queues,$queue['queue_name']);
					}
				}
			}

		}
		return $user_queues;




	}
	public function is_supervisor() {
		if (!$this->get_supervisor_id()) {
			return true;
		}
		return false;

	}
	public function enable() {
		$sql = "UPDATE users SET user_enabled='1' WHERE user_id=:user_id LIMIT 1";
		$parameters = array (
			":user_id"=>$this->get_user_id()
		);
		$this->db->non_select_query($sql,$parameters);
		$this->enabled = true;
		return true;
	}
	public function disable() {
		$supervising_users = $this->get_supervising_users();
		$message;
		$error = false;
		if (count($supervising_users)) {
			$message = "Unable to delete user.  User is supervising " . count($supervising_users) . " other users.";
			$error = true;
		}		
		if (is_dir($this->default_data_dir()->get_directory())) {
                        $message = "Unable to delete user.  Home folder " . $this->default_data_dir()->get_directory() . " still exists.";
                        $error = true;
                }
                if (count($this->get_owned_projects()) > 1) {
                        $message = "Unable to delete user.  User is the owner of active projects";
                        $error = true;
                }

		if (!$error) {
			$sql = "UPDATE users SET user_enabled='0' WHERE user_id=:user_id LIMIT 1";
			$parameters = array( 
				":user_id"=>$this->get_user_id()
			);
			$this->enabled = false;
			$this->db->non_select_query($sql,$parameters);
			$this->default_project()->disable();
			$this->default_data_dir()->disable();
			
			$message = "User successfully deleted";
			return array('RESULT'=>true,'MESSAGE'=>$message);
		}
		else {
			return array('RESULT'=>false,'MESSAGE'=>$message);
		}

	}
	public function set_supervisor($supervisor_id) {
		$sql = "UPDATE users SET user_supervisor=:supervisor_id WHERE user_id=:user_id LIMIT 1";
		$parameters = array(
			':supervisor_id'=>$supervisor_id,
			':user_id'=>$this->get_user_id()
		);
		$this->db->non_select_query($sql,$parameters);
		//gets supervisors username
		$supervisor_sql = "SELECT user_name FROM users WHERE user_id=:supervisor_id LIMIT 1";
		$supervisor_parameters = array(
			':supervisor_id'=>$supervisor_id
		);
		$result = $this->db->query($supervisor_sql,$supervisor_parameters);

		$this->supervisor_id = $supervisor_id;
		$this->supervisor_name = $result[0]['user_name'];;
		return true;
	}
	public function get_supervising_users() {
		if ($this->is_supervisor()) {
			$sql = "SELECT users.* ";
			$sql .= "FROM users ";
			$sql .= "WHERE user_supervisor=:supervisor_id AND user_enabled='1' ";
			$sql .= "AND user_admin='0' ORDER BY user_name ASC";
			$parameters = array(
				':supervisor_id'=>$this->get_user_id()
			);
			return $this->db->query($sql,$parameters);
		}
		return array();
	}

	public function is_admin() {
		return $this->admin;
	}

	public function is_user() {
		return !$this->admin;

        }
	
	public function set_admin($admin) {
		$sql = "UPDATE users SET user_admin=:admin ";
		$sql .= "WHERE user_id=:user_id LIMIT 1";
		$parameters = array(
			':admin'=>$admin,
			':user_id'=>$this->get_user_id()
		);
		$result = $this->db->non_select_query($sql,$parameters);
		if ($result) {
			$this->admin = $admin;
		}
		return $result;
	}

	//is_supervising_user()
	//$user_id - user id to test if you are the supervisor
	//returns true if user is supervising the respected user, false otherwise
	public function is_supervising_user($user_id) {
		if ($this->is_supervisor()) {
			$sql = "SELECT user_supervisor as supervisor_id ";
			$sql .= "FROM users ";
			$sql .= "WHERE user_id=:user_id AND user_enabled='1' ";
			$sql .= "LIMIT 1";
			$parameters = array(
				':user_id'=>$user_id
			);
			$result = $this->db->query($sql,$parameters);
			if ($result[0]['supervisor_id'] == $this->get_user_id()) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		return FALSE;

	}
	public function email_bill($admin_email,$year,$month,$website_url) {

		if (!$this->ldap->is_ldap_user($this->get_username())) {
			throw new \Exception("Email Bill - User " . $this->get_username() . " not in ldap");
			return false;
		}
		elseif ($this->get_email() == "") {
			throw new \Exception("Email Bill - User " . $this->get_username() . " email is not set");
			return false;
		}
		else {
			$bill_month = DateTime::createFromFormat("Y-m-d H:i:s",$year . "-" . $month . "-01 00:00:00");
		

			$subject = "Biocluster Accounting Bill - " . $bill_month->format('F') . " - " . $bill_month->format('Y');

			$to = $this->get_email();
			if (settings::get_debug()) {
				$to = $admin_email;
			}
			$job_summary = $this->get_jobs_summary($month,$year);
			$data_summary = $this->get_data_summary($month,$year);
			if ($this->is_supervisor()) {
				foreach ($this->get_supervising_users() as $supervising_user) {
					$supervising_user_object = new user($this->db,$this->ldap,$supervising_user['user_id']);
					$supervising_user_jobs = $supervising_user_object->get_jobs_summary($month,$year);
					$supervising_user_data = $supervising_user_object->get_data_summary($month,$year);
					foreach ($supervising_user_jobs as $user_jobs) {
						array_push($job_summary,$user_jobs);
					}
					foreach ($supervising_user_data as $user_data) {
						array_push($data_summary,$user_data);
					}
				}
			}
			$twig_variables = array(
        	                'css' => settings::get_email_css_contents(),
                	        'month' => $bill_month->format('F'),
                        	'year' => $bill_month->format('Y'),
	                        'full_name' => $this->get_full_name(),
        	                'username' => $this->get_username(),
                	        'website_url' => $website_url,
                        	'jobs_table' => $job_summary,
	                        'data_table' => $data_summary,
				'admin_email'=> $admin_email
	                );

			$loader = new \Twig\Loader\FilesystemLoader(settings::get_twig_dir());
			$twig = new \Twig\Environment($loader);

			if (file_exists(settings::get_twig_dir() . "/custom/" . self::USER_BILL_TWIG)) {
				$html_message = $twig->render("custom/" . self::USER_BILL_TWIG,$twig_variables);
			}
			else {
				$html_message = $twig->render("default/" . self::USER_BILL_TWIG,$twig_variables);
			}

			$email = new \IGBIllinois\email(settings::get_smtp_host(),
						settings::get_smtp_port(),
						settings::get_smtp_username(),
						settings::get_smtp_password());

			$email->set_replyto_emails(settings::get_admin_email());
			$email->set_to_emails($to);
			try {
				$result = $email->send_email(settings::get_from_email(),$subject,"",$html_message,settings::get_from_name());
				$message = "Email Bill - User " . $this->get_username() . " successfully sent to " . $this->get_email();

			} catch (Exception $e) {
				throw new \Exception("Email BIll - User " . $this->get_username() . " Error sending mail. " . $e->getMessage());
				return false;;
			}
			return $result;
		}

	}


	public function authenticate($password) {
		$result = false;
                if (($this->ldap->authenticate($this->get_username(),$password)) && ($this->get_user_exist($this->user_name))) {
                        $result = true;

                }
                return $result;
        }

	//permission()
        //$user_id - id of user to see if you have permissions to view his details
        //returns true if you do have permissions, false otherwise
        public function permission($user_id) {
                if ($this->is_admin()) {
                        return TRUE;
                }
                elseif ($this->get_user_id() == $user_id) {
                        return TRUE;
                }
                elseif ($this->is_supervising_user($user_id)) {
                        return TRUE;
                }
                else {
                        return FALSE;
                }

        }

	//////////////////Private Functions//////////
	private function load_by_id($id) {
		$this->id = $id;
		$this->get_user();
	}
	private function load_by_username($username) {
		$sql = "SELECT user_id FROM users WHERE user_name=:username LIMIT 1";
		$parameters = array(
			':username'=>$username
		);
		$result = $this->db->query($sql,$parameters);
		if (isset($result[0]['user_id'])) {
			$this->id = $result[0]['user_id'];
			$this->get_user();
		}
	}
	private function get_user() {

		$sql = "SELECT users.user_id, users.user_admin, users.user_name, ";
		$sql .= "users.user_firstname,users.user_lastname, ";
		$sql .= "users.user_enabled, users.user_time_created, ";
		$sql .= "supervisor.user_id as supervisor_id, supervisor.user_name as supervisor_name, ";
		$sql .= "projects.project_id as project_id, data_dir.data_dir_id as data_dir_id ";
		$sql .= "FROM users ";
		$sql .= "LEFT JOIN users AS supervisor ON supervisor.user_id=users.user_supervisor ";
		$sql .= "LEFT JOIN projects ON projects.project_owner=users.user_id ";
		$sql .= "LEFT JOIN data_dir ON data_dir.data_dir_project_id=projects.project_id ";
		$sql .= "WHERE users.user_id=:user_id ";
		$sql .= "AND projects.project_default='1' ";
		$sql .= "AND data_dir.data_dir_default='1' LIMIT 1";
		$parameters = array(
			':user_id'=>$this->get_user_id()
		);
		$result = $this->db->query($sql,$parameters);
		if (count($result)) {
			$this->user_name = $result[0]['user_name'];
			$this->admin = $result[0]['user_admin'];
			$this->firstname = $result[0]['user_firstname'];
			$this->lastname = $result[0]['user_lastname'];
			$this->time_created = $result[0]['user_time_created'];
			$this->supervisor_name = $result[0]['supervisor_name'];
                        $this->supervisor_id = $result[0]['supervisor_id'];
			$this->default_project_id = $result[0]['project_id'];
			$this->default_data_dir_id = $result[0]['data_dir_id'];
			$this->supervisor_id = $result[0]['supervisor_id'];
			if (!$result[0]['supervisor_id']) {
				$this->supervisor_id = 0;	
			}
			$this->enabled = $result[0]['user_enabled'];
			$this->email = $this->ldap->get_email($this->get_username());
		}
	}
	private function get_user_exist($username) {

		$sql = "SELECT 1 FROM users WHERE user_name=:username AND user_enabled='1' LIMIT 1";
		$parameters = array(
			':username'=>$username
		);
		$result = $this->db->query($sql,$parameters);
		if ($result) {
			return true;
		}
		return false;

	}

	private function is_disabled($username) {
		$sql = "SELECT count(1) as count FROM users WHERE user_name=:username ";
		$sql .= "AND user_enabled='0' LIMIT 1";

		$parameters = array(
                        ':username'=>$username
                );
		$result = $this->db->query($sql,$parameters);
		if ($result[0]['count']) {
			return true;
		}
		return false;

	}

}


?>
