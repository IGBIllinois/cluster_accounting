<?php

class job {

	////////////////Private Variables//////////

	protected $db; //mysql database object
	protected $id; //job id number
	protected $queue; //queue object
	protected $project; //project object
	protected $hostname;
	protected $name;
	protected $job_number_full;
	protected $job_number; //torque job number
	protected $job_number_array;
	protected $submission_time;
	protected $start_time;
	protected $end_time;
	protected $queued_time;
	protected $elapsed_time;
	protected $wallclock_time;
	protected $cpu_time;
	protected $slots;
	protected $reserved_mem;
	protected $used_mem;
	protected $maxvmem;
	protected $username;
	protected $user_id;
	protected $total_cost;
	protected $billed_cost;
	protected $cfop_id;
	protected $cfop;
	protected $activity_code;
	protected $exit_status;
	protected $submitted_project;
	protected $exec_hosts = array();
	protected $job_script = "No Job Script Available";
	protected $job_script_exists = 0;
	protected $reserved_gpu;
	protected $job_state;
	protected $exit_status_codes = array('0'=>'JOB_EXEC_OK',
			'-1'=>'JOB_EXEC_FAIL1',
			'-2'=>'JOB_EXEC_FAIL2',
			'-3'=>'JOB_EXEC_RETRY',
			'-4'=>'JOB_EXEC_INITABT',
			'-5'=>'JOB_EXEC_INITRST',
			'-6'=>'JOB_EXEC_INITRMG',
			'-7'=>'JOB_EXEC_BADRESRT',
			'-8'=>'JOB_EXEC_CMDFAIL',
			'1'=>'Job Script or Executable Returned an Error',
			'8'=>'Floating Point Exception',
			'126'=>'Command Cannot Execute',
			'127'=>'Command Not Found',
			'139'=>'Segmentation Fault/Memory Error',
			'271'=>'A Resource Limit was Exceeded or Job Killed with qdel'
	);
	////////////////Public Functions///////////

	public function __construct($db,$job_number = 0) {
		$this->db = $db;
		if ($job_number != 0) {
			$this->load($job_number);
		}

	}
	public function __destruct() {
	}

	public function load($job_number) {
		$split_job = self::split_job_number($job_number);
		$this->job_number = $split_job['job_number'];
		$this->job_number_array = $split_job['job_number_array'];
		$this->get_job();
	}

	public function create($job_data,$ldap) {
		$job_number = $job_data['job_number'];
		$split_job = self::split_job_number($job_data['job_number']);
		if (!$this->job_exists($job_number)) {
			$this->queue = new queue($this->db,0,$job_data['job_submission_time'],$job_data['job_queue_name']);
			$user = new user($this->db,$ldap,0,$job_data['job_user']);
			
			//Checks if user is a member of the project, if not, then use user's default project.
			if ($user->is_project_member($job_data['job_project'])) {
				$this->project = new project($this->db,0,$job_data['job_project']);
			}
			else {
				$this->project = new project($this->db,0,$job_data['job_user']);	
			}
			
			$message = "";
			$error = false;
			if (!$this->queue->get_queue_id()) {
				$error = true;
				$message = "ERROR: Job Number: " . $job_number . " - Queue " . $job_data['job_queue_name'] . " does not exist.\n";
			}

			if (!$this->project->get_project_id()) {
				$error = true;
				$message .= "ERROR: Job Number: " . $job_number . " - Project " . $job_data['job_project'] . " does not exist.\n";
			}
			if (!$user->get_user_id()) {
				$error = true;
				$message .= "ERROR: Job Number: " . $job_number . " - User " . $job_data['job_user'] . " does not exist.";
			}

			if ($error) {
				return array('RESULT'=>false,
						'MESSAGE'=>$message);

			}
			else {
				if ($job_data['job_used_mem'] > $job_data['job_reserved_mem']) {
					$mem = $job_data['job_used_mem'];
				}
				else {
					$mem = $job_data['job_reserved_mem'];
				}
				$cost = $this->queue->calculate_cost($job_data['job_cpu_time'],$job_data['job_ru_wallclock'],
						$job_data['job_slots'],$mem,$job_data['job_start_time'],$job_data['job_end_time'],$job_data['job_gpu']);
				$bill_cost = 0;
				if ($this->project->get_billtype() != project::BILLTYPE_NO_BILL) {
					$bill_cost = $cost;
				}
				
				$job_data['job_number'] = $split_job['job_number'];
				if ($split_job['job_number_array'] != "") {
					$job_data['job_number_array'] = $split_job['job_number_array'];	
				}
				$job_data['job_total_cost'] = $cost;
				$job_data['job_billed_cost'] = $bill_cost;
				$job_data['job_user_id'] = $user->get_user_id();
				$job_data['job_project_id'] = $this->project->get_project_id();
				$job_data['job_queue_id'] = $this->queue->get_queue_id();
				$job_data['job_cfop_id'] = $this->project->get_cfop_id();
				$job_data['job_queue_cost_id'] = $this->queue->get_queue_cost_id();
				$job_id = $this->db->build_insert("jobs",$job_data);
				if ($job_id) {
					return array('RESULT'=>true,
						'job_id'=>$job_id,'MESSAGE'=>"Job Number: " . $job_number . " - User: " . $job_data['job_user'] . " - Successfully added to database");
				}
				else {
					return array('RESULT'=>0,
						'MESSAGE'=>'ERROR: Error adding job ' . $job_number);
				}
				
			}
		}
		else {
			return array('RESULT'=>0,
				'MESSAGE'=> "Job Number: " . $job_number . " already exists in database");
		}

	}

	public function get_job_id() {
		return $this->id;
	}
	public function get_queue_name() {
		return $this->queue_name;
	}
	public function get_hostname() {
		return $this->hostname;
	}
	public function get_job_name() {
		return $this->name;
	}
	public function get_username() {
		return $this->username;
	}
	public function get_user_id() {
		return $this->user_id;
	}
	public function get_full_job_number() {
		return $this->job_number_full;
	}
	public function get_job_number() {
		return $this->job_number;
	}
	
	public function get_job_number_array() {
		return $this->job_number_array;

	}
	public function get_submission_time() {
		return $this->submission_time;
	}
	public function get_start_time() {
		return $this->start_time;
	}
	public function get_end_time() {
		return $this->end_time;
	}
	public function get_queued_time() {
		return $this->queued_time;
	}
	public function get_queued_time_hours() {
		return $this->format_time($this->get_queued_time());
	}
	public function get_elapsed_time() {
		return $this->elapsed_time;
	}
	public function get_elapsed_time_hours() {
		return $this->format_time($this->get_elapsed_time());
	}
	public function get_wallclock_time() {
		return $this->wallclock_time;
	}
	public function get_wallclock_time_hours() {
		return $this->format_time($this->get_wallclock_time());
	}
	public function get_slots() {
		return $this->slots;
	}
	public function get_cpu_time() {
		return $this->cpu_time;
	}
	public function get_cpu_time_hours() {
		return $this->format_time($this->get_cpu_time());
	}
	public function get_reserved_mem() {
		return $this->reserved_mem;
	}
	public function get_reserved_mem_gb() {
		return round($this->get_reserved_mem()/1073741824,2);
	}
	public function get_used_mem() {
		return $this->used_mem;
	}
	public function get_used_mem_gb() {
		return round($this->get_used_mem()/1073741824,2);
	}
	public function get_maxvmem() {
		return $this->maxvmem;
	}
	public function get_maxvmem_gb() {
		return round($this->get_maxvmem()/1073741824,2);
	}
	public function get_total_cost() {
		return $this->total_cost;
	}
	public function get_billed_cost() {
		return $this->billed_cost;
	}
	public function get_cfop_id() {
		return $this->cfop_id;
	}
	public function get_cfop() {
		return $this->cfop;
	}
	public function get_activity_code() {
		return $this->activity_code;
	}
	public function get_formated_total_cost() {
		return number_format($this->get_total_cost(),2);
	}
	public function get_formated_billed_cost() {
		return number_format($this->get_billed_cost(),2);
	}
	public function get_submitted_project() {
		return $this->submitted_project;
	}
	public function get_reserved_gpu() {
		return $this->reserved_gpu;
	}
	public function get_project() {
		return $this->project;
		
	}
	public function get_job_state() {
		return $this->job_state;
		
	}
	public function get_exec_hosts() {
		return $this->exec_hosts;
	}
	public function get_job_script() {
		return urldecode($this->job_script);
	}
	public function get_job_script_exists() {
		return $this->job_script_exists;
	}
	public function get_exit_status() {
		if (isset($this->exit_status_codes[$this->exit_status])) {
			return $this->exit_status . " - " . $this->exit_status_codes[$this->exit_status];
		}
		else { return $this->exit_status;
		}
	}

	public function set_billed_cost($cost) {
		$verify_cost = $this->verify_cost($cost);
		$valid = true;
		$message = "";
		if (!$verify_cost['RESULT']) {
			$message = $verify_cost['MESSAGE'];
			$valid = false;
		}
		if (!$this->get_project()->get_billtype() == project::BILLTYPE_NO_BILL) {
			$message = "This project is not a billable project";
			$valid = false;
		}
		
		if ($valid) {
			$sql = "UPDATE jobs SET job_billed_cost=:cost ";
			$sql .= "WHERE job_id=:job_id LIMIT 1";
			$parameters = array(
				':cost'=>$cost,
				':job_id'=>$this->get_job_id()
			);
			$result = $this->db->non_select_query($sql,$parameters);
			if ($result) {
				$this->billed_cost = $cost;
				$message = "Billed Cost successfully changed";
			}
		}
		return array('RESULT'=>$valid,'MESSAGE'=>$message);

	}

	public function set_project($project_id) {
		$project = new project($this->db,$project_id);
		$sql = "UPDATE jobs SET job_project_id=:project_id";
		$sql .= ",job_cfop_id=:cfop_id ";
		$sql .= "WHERE job_id=:job_id LIMIT 1";
		$parameters = array(
			':project_id'=>$project_id,
			':cfop_id'=>$project->get_cfop_id(),
			':job_id'=>$this->get_job_id()

		);
		$result = $this->db->non_select_query($sql,$parameters);
		if ($result) {
			$message = "Project Successfully updated";
			$this->project =  $project;
			return array('RESULT'=>true,'MESSAGE'=>$message);
		}
		return array('RESULT'=>false,'MESSAGE'=>'Failed updating project');

	}

	public function set_cfop($cfop_id) {
		$sql = "UPDATE jobs SET job_cfop_id=:cfop_id ";
		$sql .= "WHERE job_id=:job_id LIMIT 1";
		$parameters = array(
			':cfop_id'=>$cfop_id,
			':job_id'=>$this->get_job_id()
		);
		$result = $this->db->non_select_query($sql);
		if ($result) {
			$message = "CFOP successfully updated.";
			$this->cfop_id = $cfop_id;
			$cfop = functions::get_cfop($this->db,$cfop_id);
			$this->cfop = $cfop[0]['cfop_value'];
			$this->activity_code = $cfop[0]['cfop_activity'];
			return array('RESULT'=>true,'MESSAGE'=>$message);
		}
		return array('RESULT'=>false,'MESSAGE'=>'Failed updating CFOP');

	}
	public function set_new_cfop($cfop,$activity,$hide_cfop) {
		$valid = 1;
		$message = "";
		if (!\IGBIllinois\cfop::verify_format($cfop,$activity)) {
			$message .= "Invalid CFOP";
			$valid =0;
		}
		if ($valid) {

			$insert_array = array('cfop_project_id'=>$this->project->get_project_id(),
				'cfop_value'=>$cfop,
				'cfop_activity'=>$activity,
				'cfop_restricted'=>$hide_cfop);
			$result = $this->db->build_insert('cfops',$insert_array);
			$this->set_cfop($result);
			return array('RESULT'=>true,'cfop_id'=>$result,'MESSAGE'=>'CFOP successfully updated');

		}
		return array('RESULT'=>false,'MESSAGE'=>$message);

	}
	public function job_exists($job_number) {
		$split_job = self::split_job_number($job_number);
		$parameters[':job_number'] = $split_job['job_number'];
		if ($split_job['job_number_array'] == "") {
                        $sql = "SELECT count(1) AS count FROM jobs ";
                        $sql .= "WHERE job_number=:job_number ";
                        $sql .= "AND ISNULL(job_number_array) LIMIT 1";

		}
		else {
			$sql = "SELECT count(1) AS count FROM jobs ";
			$sql .= "WHERE job_number=:job_number ";
			$sql .= "AND job_number_array=:job_number_array LIMIT 1";
			$parameters[':job_number_array'] = $split_job['job_number_array'];
		}
		$result = $this->db->query($sql,$parameters);
		if ($result[0]['count']) { 
			return true;
		}
		return false;
	}

	/////////////////Private Functions///////////

	protected function get_job() {
		$parameters[':job_number'] = $this->get_job_number();
		if ($this->get_job_number_array() == "") {
	                $sql = "SELECT * FROM job_info ";
	                $sql .= "WHERE job_number=:job_number AND ";
        	        $sql .= "ISNULL(job_number_array) LIMIT 1";

		}
		else {
			$sql = "SELECT * FROM job_info ";
			$sql .= "WHERE job_number=:job_number AND ";
			$sql .= "job_number_array=:job_number_array LIMIT 1";
			$parameters[':job_number_array'] = $this->get_job_number_array();
			
		}
		$result = $this->db->query($sql,$parameters);
		if ($result) {
			$this->id = $result[0]['id'];
			$this->queue_name = $result[0]['queue_name'];
			$this->name = $result[0]['job_name'];
			$this->job_number_full = $result[0]['job_number_full'];
			$this->job_number = $result[0]['job_number'];
			$this->job_number_array = $result[0]['job_number_array'];
			$this->slots = $result[0]['slots'];
			$this->username = $result[0]['username'];
			$this->user_id = $result[0]['user_id'];
			$this->cfop = $result[0]['cfop'];
			$this->activity_code = $result[0]['activity_code'];
			$this->total_cost = $result[0]['total_cost'];
			$this->billed_cost = $result[0]['billed_cost'];
			$this->submission_time = $result[0]['submission_time'];
			$this->queued_time = $result[0]['queued_time'];
			$this->start_time = $result[0]['start_time'];
			$this->end_time = $result[0]['end_time'];
			$this->elapsed_time = $result[0]['elapsed_time'];
			$this->wallclock_time = $result[0]['wallclock_time'];
			$this->cpu_time = $result[0]['cpu_time'];
			$this->reserved_mem = $result[0]['reserved_mem'];
			$this->used_mem = $result[0]['used_mem'];
			$this->maxvmem = $result[0]['maxvmem'];
			$this->exit_status = $result[0]['exit_status'];
			$this->submitted_project = $result[0]['submitted_project'];
			$this->queue = new queue($this->db,$result[0]['queue_id'],$result[0]['submission_time']);
			$this->project = new project($this->db,$result[0]['project_id']);
			$this->set_exec_hosts_var($result[0]['exec_hosts']);
			$this->reserved_gpu = $result[0]['gpu'];
			$this->job_state = $result[0]['state'];
			if ($result[0]['qsub_script']) {
				$this->job_script = $result[0]['qsub_script'];
				$this->job_script_exists = 1;
			}
		}
	}

	protected static function split_job_number($in_job_number) {
		$job_number_array = 0;
	
		//Torque Job Array	
                if (strpos($in_job_number,"[")) {
                        $hyphen_pos = strrpos($in_job_number,"[");
                        $job_number_array = substr($in_job_number, $hyphen_pos+1);
                        $job_number_array = substr($job_number_array,0,strlen($job_number_array)-1);
                        $job_number = substr($in_job_number,0,$hyphen_pos);
			return array('job_number'=>$job_number,'job_number_array'=>$job_number_array);
                }
		//Slurm Job Array
		elseif (strpos($in_job_number,"_")) {
			$underscore_pos = strpos($in_job_number,"_");
			$job_number_array = substr($in_job_number,$underscore_pos+1);
			$job_number = substr($in_job_number,0,$underscore_pos);
			return array('job_number'=>$job_number,'job_number_array'=>$job_number_array);


		}
		//Neither
		else {
			return array('job_number'=>$in_job_number,'job_number_array'=>"");
		}

	}
	
	protected function set_exec_hosts_var($exec_hosts) {
		if (strlen($exec_hosts)) {
			$exec_hosts_array = explode("+",$exec_hosts);
			sort($exec_hosts_array,SORT_STRING);
			$this->exec_hosts = $exec_hosts_array;
		}

	}
	protected function verify_cost($cost) {
		$valid = true;
		$message = "";
		if (!is_numeric($cost)) {
			$message = "Please verify the cost";
			$valid = false;
		}
		return array('RESULT'=>$valid,'MESSAGE'=>$message);

	}


	protected function format_time($t,$f=':') // t = seconds, f = separator 
	{
		return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
	}
}

?>
