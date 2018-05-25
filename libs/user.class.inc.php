<?php
class user {

	////////////////Private Variables//////////

	private $db; //mysql database object
	private $id;
	private $user_name;
	private $full_name;
	private $supervisor_name;
	private $supervisor_id;
	private $enabled;
	private $time_created;
	private $ldap;
	private $default_project; //default project object
	private $default_data_dir; //default data_dir object
	private $email;
	private $admin;
	private $default_project_id;
	private $default_data_dir_id;
	////////////////Public Functions///////////

	public function __construct($db,$ldap,$id = 0,$username = "") {
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
	public function create($username,$supervisor_id,$admin,$bill_project,$cfop = "",$activity = "",$hide_cfop = 0) {
		$username = trim(rtrim($username));
		

		$error = false;
		//Verify Username
		if ($username == "") {
			$error = true;
			$message = "<div class='alert'>Please enter a username</div>";
		}
		elseif (preg_match('/[A-Z]/',$username)) {
			$error = true;
			$message = "<div class='alert'>Username can only be lowercase</div>";
		}
		elseif ($this->get_user_exist($username)) {
			$error = true;
			$message .= "<div class='alert'>User already exists in database</div>";
		}
		elseif (!$this->ldap->is_ldap_user($username)) {
			$error = true;
			$message = "<div class='alert'>User does not exist in LDAP database.</div>";
		}

		if ($supervisor_id == "-1") {
			$error = true;
			$message .= "<div class='alert'>Please select a supervisor.</div>";
		}
		//Verify CFOP/Activty Code
		$project = new project($this->db);
		if (!$project->verify_cfop($cfop) && $bill_project) {
			$error = true;
			$message .= "<div class='alert'>Invalid CFOP.</div>";
		}

		if (!$project->verify_activity_code($activity) && $bill_project) {
			$error = true;
			$message .= "<div class='alert'>Invalid Activity Code.</div>";
		}

		//If Errors, return with error messages
		if ($error) {
			return array('RESULT'=>false,
					'MESSAGE'=>$message);
		}

		//Everything looks good, add user and default user project
		else {
		
			if ($this->is_disabled($username)) {
				$this->load_by_username($username);
				$this->enable();
				$this->set_supervisor($supervisor_id);
				$this->default_project()->enable();
				$this->default_data_dir()->enable();
				$this->default_project()->set_cfop($bill_project,$cfop,$activity,$hide_cfop);
				
			}
			else {
				$full_name = $this->ldap->get_ldap_full_name($username);
				$home_dir = $this->ldap->get_home_dir($username);
				$user_array = array('user_name'=>$username,
						'user_full_name'=>$full_name,
						'user_admin'=>$admin,
						'user_supervisor'=>$supervisor_id,
						'user_enabled'=>1
				);
				$user_id = $this->db->build_insert("users",$user_array);
				$this->load_by_id($user_id);
				$description = "default";
				$default = 1;
				$project->create($username,$username,$description,$default,$bill_project,$user_id,$cfop,$activity,$hide_cfop);
				$data_dir = new data_dir($this->db);
				$default = 1;
				$data_dir->create($project->get_project_id(),$home_dir,$default);
			}
			return array('RESULT'=>true,
					'MESSAGE'=>'User succesfully added.',
					'user_id'=>$user_id);
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
	public function get_full_name() {
		return $this->full_name;
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
	public function get_jobs_summary($start_date,$end_date) {

		$sql = "SELECT ROUND(SUM(jobs.job_total_cost),2) as total_cost, ";
		$sql .= "ROUND(SUM(jobs.job_billed_cost),2) as billed_cost, ";
		$sql .= "COUNT(1) as num_jobs, ";
		$sql .= "queues.queue_name as queue, ";
		$sql .= "projects.project_name as project, ";
		$sql .= "cfops.cfop_value as cfop, cfops.cfop_activity as activity, ";
		$sql .= "cfops.cfop_restricted as cfop_restricted ";
		$sql .= "FROM jobs ";
		$sql .= "LEFT JOIN queues ON queues.queue_id=jobs.job_queue_id ";
		$sql .= "LEFT JOIN projects ON projects.project_id=jobs.job_project_id ";
		$sql .= "LEFT JOIN cfops ON cfops.cfop_id=jobs.job_cfop_id ";
		$sql .= "WHERE DATE(jobs.job_end_time) BETWEEN '" . $start_date ."' AND '" . $end_date . "' ";
		$sql .= "AND jobs.job_user_id='". $this->get_user_id() . "' ";
		$sql .= "GROUP BY jobs.job_queue_id, jobs.job_cfop_id, jobs.job_user_id";
		$result = $this->db->query($sql);
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
		$sql .= "WHERE DATE(jobs.job_end_time) BETWEEN '" . $start_date ."' AND '" . $end_date . "' ";
		$sql .= "AND jobs.job_user_id='". $this->get_user_id() . "' ";
		$result = $this->db->query($sql);
		return $result;



	}
	public function get_data_summary($month,$year) {
		$sql = "SELECT data_dir.data_dir_path as directory, ";
		$sql .= "data_cost.data_cost_value as data_cost_value, ";
		$sql .= "data_cost.data_cost_type as data_cost_type, ";
		$sql .= "projects.project_name as project, ";
		$sql .= "ROUND((data_bill.data_bill_avg_bytes / 1099511627776),4) as terabytes, ";
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
		$sql .= "WHERE projects.project_owner='" . $this->get_user_id() . "' ";
		$sql .= "AND YEAR(data_bill.data_bill_date)='" . $year . "' ";
	        $sql .= "AND MONTH(data_bill.data_bill_date)='" . $month . "' ";
		return $this->db->query($sql);
		
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
		$sql = "UPDATE users SET user_enabled='1' WHERE user_id='" . $this->get_user_id() . "' LIMIT 1";
		$this->db->non_select_query($sql);
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
		if (!$error) {
			$sql = "UPDATE users SET user_enabled='0' WHERE user_id='" . $this->get_user_id() . "' LIMIT 1";
			$this->enabled = false;
			$this->db->non_select_query($sql);
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
		$sql = "UPDATE users SET user_supervisor='" . $supervisor_id . "' WHERE user_id='" . $this->get_user_id() . "'";
		$this->db->non_select_query($sql);
		//gets supervisors username
		$supervisor_sql = "SELECT user_name FROM users WHERE user_id='" . $supervisor_id . "' LIMIT 1";
		$result = $this->db->query($supervisor_sql);

		$this->supervisor_id = $supervisor_id;
		$this->supervisor_name = $result[0]['user_name'];;
		$this->db->non_select_query($sql);
		return true;
	}
	public function get_supervising_users() {
		if ($this->is_supervisor()) {
			$sql = "SELECT users.* ";
			$sql .= "FROM users ";
			$sql .= "WHERE user_supervisor='" . $this->get_user_id() . "' AND user_enabled='1' ";
			$sql .= "AND user_admin='0' ORDER BY user_name ASC";
			return $this->db->query($sql);
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
		$sql = "UPDATE users SET user_admin='" . $admin . "' ";
		$sql .= "WHERE user_id='" . $this->get_user_id() . "' LIMIT 1";
		$result = $this->db->non_select_query($sql);
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
			$sql .= "WHERE user_id='" . $user_id . "' AND user_enabled='1' ";
			$sql .= "LIMIT 1";
			$result = $this->db->query($sql);
			if ($result[0]['supervisor_id'] == $this->get_user_id()) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		return FALSE;

	}
	public function email_bill($admin_email,$year,$month) {

		if (!$this->ldap->is_ldap_user($this->get_username())) {
			functions::log("Email Bill - User " . $this->get_username() . " not in ldap");
		}
		elseif ($this->get_email() == "") {
			functions::log("Email Bill - User " . $this->get_username() . " email is not set");
		}
		else {
			$start_date = $year . $month . "01";
			$end_date = $year . $month . date('t',strtotime($start_date));

		
			$user_stats = new user_stats($this->db,$this->get_user_id(),$start_date,$end_date);

			$subject = "Biocluster Accounting Bill - " . functions::get_pretty_date($start_date) . "-" . functions::get_pretty_date($end_date);
			$to = $this->get_email();
			$from = $admin_email;
			$html_message = "<!DOCTYPE html>";
			$html_message .= "<html lang='en'>"; 
			$html_message = "<head><style>";
			$html_message .= file_get_contents('../vendor/components/bootstrap/css/bootstrap.min.css');
			$html_message .= "</style></head>";
			$html_message .= "<body><div class='container-fluid'><div class='span12'>";
			$html_message .= "<p>Biocluster Accounting Bill - " . functions::get_pretty_date($start_date) . "-" . functions::get_pretty_date($end_date) . "</p>";
			$html_message .= "<br>Name: " . $this->get_full_name();
			$html_message .= "<br>Username: " . $this->get_username();
			$html_message .= "<br>Start Date: " . functions::get_pretty_date($start_date);
			$html_message .= "<br>End Date: " . functions::get_pretty_date($end_date);
			$html_message .= "<br>Number of Jobs: " . $user_stats->get_num_jobs();
			$html_message .= "<p>Below is your bill.  You can go to <a href='https://biocluster.igb.illinois.edu/accounting/'> ";
			$html_message .= "https://biocluster.igb.illinois.edu/accounting/</a>";
			$html_message .= "to view a detail listing of your jobs.";
			$html_message .= "<h4>Cluster Usage</h4>";
			$html_message .= $this->get_jobs_table($start_date,$end_date);
			$html_message .= "<h4>Data Usage</h4>";	
			$html_message .= $this->get_data_table($month,$year);
			$html_message .= "</div></body></html>";
		
			$extraheaders = array("From"=>$from,
					"Subject"=>$subject
			);
			$message = new Mail_mime();
			$message->setHTMLBody($html_message);
			$headers= $message->headers($extraheaders);
			$body = $message->get();
			$mail = Mail::factory("mail");
			$result = $mail->send($to,$headers,$body);
			if (PEAR::isError($result)) { 
				functions::log("Email Bill - User " . $this->get_username() . " Error sending mail. " . $mail->getMessage());
			}
			else {
				functions::log("Email Bill - User " . $this->get_username() . " successfully sent to " . $this->get_email());
			}
			
		}

	}

	public function get_jobs_table($start_date,$end_date) {
		$jobs_summary = $this->get_jobs_summary($start_date,$end_date);
		$jobs_html = "<p><table class='table table-striped table-bordered table-condensed'>";
		if (count($jobs_summary)) {
                        $jobs_html .= "<tr><th>Queue</th><th>Project</th>";
                        $jobs_html .= "<th>Cost</th><th>Billed Amount</th><th>CFOP</th><th>Activity Code</th></tr>";
                        foreach ($jobs_summary as $summary) {
                                $jobs_html .= "<tr>";
                                $jobs_html .= "<td>" . $summary['queue'] . "</td>";
                                $jobs_html .= "<td>" . $summary['project'] . "</td>";
                                $jobs_html .= "<td>$" . number_format($summary['total_cost'],2) . "</td>";
                                $jobs_html .= "<td>$" . number_format($summary['billed_cost'],2) . "</td>";
                                if (!$summary['cfop_restricted']) {
                                        $jobs_html .= "<td>" . $summary['cfop'] . "</td>";
                                        $jobs_html .= "<td>" . $summary['activity'] . "</td>";
                                }
                                else {
                                        $jobs_html .= "<td colspan='2'>RESTRICTED</td>";
                                }
                                $jobs_html .= "</tr>";
                        }
                }
                else {
                        $jobs_html .= "<tr><th>No Jobs</th></tr>";

                }
		$jobs_html .= "</table>";
		return $jobs_html;



	}

	public function get_data_table($month,$year) {

		$data_summary = $this->get_data_summary($month,$year);
		$data_html = "<p><table class='table table-striped table-bordered table-condensed'>";
		if (count($data_summary)) {
                        $data_html .= "<tr><th>Directory</th>";
                        $data_html .= "<th>Cost ($/TB)</th>";
                        $data_html .= "<th>Project</th>";
                        $data_html .= "<th>Terabytes</th>";
                        $data_html .= "<th>Cost</th>";
                        $data_html .= "<th>Billed Amount</th>";
                        $data_html .= "<th>CFOP</th>";
                        $data_html .= "<th>Activity Code</th>";
                        $data_html .= "</tr>";
                        foreach ($data_summary as $data) {
                                $data_html .= "<tr>";
                                $data_html .= "<td>" . $data['directory'] . "</td>";
                                $data_html .= "<td>$" . number_format($data['data_cost_value'],2) . "</td>";
                                $data_html .= "<td>" . $data['project'] . "</td>";
                                $data_html .= "<td>" . $data['terabytes'] . "</td>";
                                $data_html .= "<td>$" . number_format($data['total_cost'],2) . "</td>";
                                $data_html .= "<td>$" . number_format($data['billed_cost'],2) . "</td>";
                                if (!$data['cfop_restricted']) {
                                        $data_html .= "<td>".  $data['cfop'] . "</td>";
                                        $data_html .= "<td>" . $data['activity_code'] . "</td>";
                                }
                                else {
                                        $data_html .= "<td colspan='2'>RESTRICTED</td>";
                                }
                                $data_html .= "</tr>";


                        }
                }
                else {
                        $data_html .= "<tr><td>No Data Usage.</td></tr>";
                }
		$data_html .= "</table>";
		return $data_html;


	}
	public function authenticate($password) {
		$result = false;
                $rdn = $this->get_user_rdn();
                if (($this->ldap->bind($rdn,$password)) && ($this->get_user_exist($this->user_name))) {
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
		$sql = "SELECT user_id FROM users WHERE user_name = '" . $username . "' LIMIT 1";
		$result = $this->db->query($sql);
		if (isset($result[0]['user_id'])) {
			$this->id = $result[0]['user_id'];
			$this->get_user();
		}
	}
	private function get_user() {

		$sql = "SELECT users.user_id, users.user_admin, users.user_name, ";
		$sql .= "users.user_full_name, users.user_enabled, users.user_time_created, ";
		$sql .= "supervisor.user_id as supervisor_id, supervisor.user_name as supervisor_name, ";
		$sql .= "projects.project_id as project_id, data_dir.data_dir_id as data_dir_id ";
		$sql .= "FROM users ";
		$sql .= "LEFT JOIN users AS supervisor ON supervisor.user_id=users.user_supervisor ";
		$sql .= "LEFT JOIN projects ON projects.project_owner=users.user_id ";
		$sql .= "LEFT JOIN data_dir ON data_dir.data_dir_project_id=projects.project_id ";
		$sql .= "WHERE users.user_id='" . $this->id . "' ";
		$sql .= "AND projects.project_default='1' ";
		$sql .= "AND data_dir.data_dir_default='1' LIMIT 1";
		$result = $this->db->query($sql);
		if (count($result)) {
			$this->user_name = $result[0]['user_name'];
			$this->admin = $result[0]['user_admin'];
			$this->full_name = $result[0]['user_full_name'];
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

		$sql = "SELECT COUNT(1) as count FROM users WHERE user_name='" . $username . "' AND user_enabled='1'";
		$result = $this->db->query($sql);
		return $result[0]['count'];

	}
	private function get_disable_user_id($username) {

		
		$sql = "SELECT user_id FROM users WHERE user_name='" . $username . "' AND user_enabled='0'";
		$result = $this->db->query($sql);
		if (count($result)) {
			return $result[0]['user_id'];
		}
		else {
			return false;
		}
	}

	private function get_user_rdn() {
                $filter = "(uid=" . $this->get_username() . ")";       
                $attributes = array('dn');
                $result = $this->ldap->search($filter,'',$attributes);
                if (isset($result[0]['dn'])) {
                        return $result[0]['dn'];
                }
                else {
                        return false;
                }
        }

	private function is_disabled($username) {
		$sql = "SELECT count(1) as count FROM users WHERE user_name='" . $username . "' ";
		$sql .= "AND user_enabled='0' LIMIT 1";
		$result = $this->db->query($sql);
		if ($result[0]['count']) {
			return true;
		}
		return false;

	}
}


?>
