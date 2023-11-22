<?php

class running_job extends job {

	////////////////Public Functions///////////

	public function create($job_data,$ldap) {
		$job_number = $job_data['job_number'];
		$split_job = $this->split_job_number($job_data['job_number']);
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

			if ($error) {
				return array('RESULT'=>false,
						'MESSAGE'=>$message);

			}
			else {
				$current_time = date('Y-m-d H:i:s');
				$mem = $job_data['job_reserved_mem'];
				$cost = $this->queue->calculate_cost($job_data['job_cpu_time'],$job_data['job_ru_wallclock'],
						$job_data['job_slots'],$mem,$job_data['job_start_time'],$current_time,$job_data['job_gpu']);
				
				$job_data['job_number'] = $split_job['job_number'];
				if ($split_job['job_number_array'] != "") {
					$job_data['job_number_array'] = $split_job['job_number_array'];	
				}
				$job_data['job_estimated_cost'] = $cost;
				$job_data['job_user_id'] = $user->get_user_id();
				$job_data['job_project_id'] = $this->project->get_project_id();
				$job_data['job_queue_id'] = $this->queue->get_queue_id();
				$job_data['job_cfop_id'] = $this->project->get_cfop_id();
				$job_data['job_queue_cost_id'] = $this->queue->get_queue_cost_id();
				$job_id = $this->db->build_insert("running_jobs",$job_data);
				if ($job_id) {
                                        return array('RESULT'=>true,
                                                'job_id'=>$job_id,'MESSAGE'=>"Running Job Number: " . $job_number . " - User: " . $job_data['job_user'] . " - Successfully added to database");
                                }
                                else {
                                        return array('RESULT'=>0,
                                                'MESSAGE'=>'ERROR: Error adding running job ' . $job_number);
                                }
		
			}
		}
		else {
			return array('RESULT'=>0,
				'MESSAGE'=> "Running Job Number: " . $job_number . " already exists in database");
		}

	}

	public function get_total_cost() {
		return 0;
	}
	public function get_billed_cost() {
		return 0;
	}

	public function get_estimated_cost() {
		return $this->estimated_cost;
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
			$this->estimated_cost = $result[0]['estimated_cost'];
			$this->submission_time = $result[0]['submission_time'];
			$this->queued_time = $result[0]['queued_time'];
			$this->start_time = $result[0]['start_time'];
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
		}
	}

	public static function truncate_table($db) {
		$sql = "truncate table running_jobs";
		$db->non_select_query($sql);

	}
}

?>
