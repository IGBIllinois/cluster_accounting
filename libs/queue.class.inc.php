<?php

class queue {

	////////////////Private Variables//////////

	private $db; //mysql database object
	private $id;
	private $queue_cost_id;
	private $time_created;
	private $name;
	private $ldap_group;
	private $description;
	private $enabled;
	private $cpu_cost;
	private $mem_cost;
	private $gpu_cost;

	///////////////Public Functions///////////

	public function __construct($db,$id = 0,$date = 0,$name = "") {
		$this->db = $db;
		if ($date == 0) {
			$date = date("Y-m-d H:i:s");
		}
		if ($id != 0) {
			$this->load_by_id($id,$date);

		}
		elseif (($id == 0) && ($name != "" )) {

			$this->load_by_name($name,$date);
		}

	}

	public function __destruct() {
	}

	//create()
	//$name - string - name of queue
	//$description - string - queue description
	//returns array with id of new queue
	//Creates new queue
	public function create($name,$description,$ldap_group,$cpu,$mem,$gpu,$ldap) {
		$errors = false;
		$message = "";
		if(!$this->verify_queue_name($name)) {
			$errors = true;
			$message .= "<div class='alert alert-danger'>Please enter a valid queue name.</div>";
		}
		if ($description == "") {
			$errors = true;
			$message .= "<div class='alert alert-danger'>Please enter a queue description.</div>";

		}
		if (!$this->verify_ldap_group($ldap,$ldap_group)) {
			$errors = true;
			$message .= "<div class='alert alert-danger'>Please enter valid LDAP group.</div>";

		}
		if (!is_numeric($cpu)) {
			$errors = true;
			$message .= "<div class='alert alert-danger'>Please enter valid CPU cost.</div>";
		}
		if (!is_numeric($mem)) {
			$errors = true;
			$message .= "<div class='alert alert-danger'>Please enter valid memory cost.</div>";

		}
		if (!is_numeric($gpu)) {
			$errors = true;
			$message .= "<div class='alert alert-danger'>Please enter valid GPU cost.</div>";
		}
		if ($errors == 0) {
			$queue_array = array('queue_name'=>$name,
					'queue_description'=>$description,
					'queue_ldap_group'=>$ldap_group);

			$this->id = $this->db->build_insert("queues",$queue_array);
			$this->update_cost($cpu,$mem,$gpu);
			$message = "<div class='alert alert-success'>Queue " . $name . " successfully created.</div>";
			return array ('RESULT'=>True,
					'ID'=>$this->get_queue_id(),
					'MESSAGE'=>$message);
		}
		else {
			return array('RESULT'=>False,
					'MESSAGE'=>$message);
		}
	}

	public function get_queue_id() {
		return $this->id;
	}
	public function get_queue_cost_id($submission_time = 0) {
		if ($submission_time == 0) {
			return $this->queue_cost_id;
		}
		elseif ($submission_time != 0) {
			$sql = "SELECT queue_cost_id FROM queue_cost ";
			$sql .= "WHERE queue_cost_queue_id='" . $this->get_queue_id() . "' ";
			$sql .= "AND queue_cost_time_created<='" . $submission_time . "' ";
			$sql .= "ORDER BY queue_cost_time_created DESC LIMIT 1";
			$result = $this->db->query($sql);
			return $result[0]['queue_cost_id'];	
		}
	}
	public function get_time_created() {
		return $this->time_created;
	}
	public function get_name() {
		return $this->name;
	}
	public function get_description() {
		return $this->description;
	}
	public function get_enabled() {
		return $this->enabled;
	}
	public function get_ldap_group() {
		return $this->ldap_group;
	}

	public function get_cpu_cost() {
		return $this->cpu_cost;
	}
	public function get_cpu_cost_per_hour() {
		return round($this->get_cpu_cost() * 3600,2);
	}
	public function get_cpu_cost_per_day() {
		return number_format(round($this->get_cpu_cost() * 3600 * 24,2),2,".","");
	}

	public function get_mem_cost() {
		return $this->mem_cost;
	}
	public function get_mem_cost_per_hour() {
		return round($this->get_mem_cost() * 3600,2);
	}
	public function get_mem_cost_per_day() {
		return number_format(round($this->get_mem_cost() * 3600 * 24,2),2,".","");
	}

	public function get_gpu_cost() {
		return $this->gpu_cost;
	}
	public function get_gpu_cost_per_hour() {
		return round($this->get_gpu_cost() * 3600,2);
	}
	public function get_gpu_cost_per_day() {
		return number_format(round($this->get_gpu_cost() * 3600 * 24,2),2,".","");
	}

	//update_cost
	//$cpu_cost - decimal - cost for cpu
	//$mem_cost - decimal - cost for memory
	//updates the cost for the queue
	public function update_cost($cpu_cost,$mem_cost,$gpu_cost) {

		$errors = false;
		$message = "";

		if (($cpu_cost == "") || (!is_numeric($cpu_cost))) {
			$errors = true;
			$message .= "<div class='alert alert-danger'>Please enter a valid processor cost.</div>";
		}

		if (($mem_cost == "") || (!is_numeric($mem_cost))) {
			$errors = true;
			$message .= "<div class='alert alert-danger'>Please enter a valid memory cost.</div>";
		}

		if (($gpu_cost == "") || (!is_numeric($gpu_cost))) {
			$errors = true;
			$message .= "<div class='alert alert-danger'>Please enter a valid GPU cost.</div>";
		}

		if ($errors) {
			return array('RESULT'=>false,
					'MESSAGE'=>$message);
		}
		else {
			$insert_array = array('queue_cost_queue_id'=>$this->get_queue_id(),
					'queue_cost_mem'=>$mem_cost,
					'queue_cost_cpu'=>$cpu_cost,
					'queue_cost_gpu'=>$gpu_cost);
			$result = $this->db->build_insert("queue_cost",$insert_array);
			if ($result) {
				return array('RESULT'=>true);
			}


		}
	}

	//enable()
	//enables the queue
	public function enable() {
		$sql = "UPDATE queues SET queue_enabled='1' WHERE queue_id='" . $this->get_queue_id() . "' LIMIT 1";
		$this->db->non_select_query($sql);
		$this->enabled = true;
		return true;
	}

	//disable()
	//disables the queue
	public function disable() {
		$sql = "UPDATE queues SET queue_enabled='0' WHERE queue_id='" . $this->get_queue_id() . "' LIMIT 1";
		$this->enabled = false;
		$this->db->non_select_query($sql);
		return true;

	}

	public function verify_queue_name($name) {
		if (!($name == "") && (preg_match('/^[-_a-z0-9]+$/',$name))) {
			return true;
		}
		return false;

	}

	public function verify_ldap_group($ldap,$ldap_group) {
		if (($ldap_group == "") || ($ldap->get_group_exists($ldap_group))) {
			return true;
		}
		return false;

	}

	public function calculate_cost($cpu_time,$wallclock_time,$slots,$mem,$start_time,$end_time,$num_gpu) {
		$elapsed_time = strtotime($end_time) - strtotime($start_time);
		$cost_array = array();
		array_push($cost_array,$this->calculate_cpu_cost($cpu_time,$wallclock_time,$slots,$start_time,$end_time));
		array_push($cost_array,$this->calculate_mem_cost($wallclock_time,$mem,$start_time,$end_time));
		array_push($cost_array,$this->calculate_gpu_cost($wallclock_time,$num_gpu,$elapsed_time));
		return max($cost_array);
	}

	public function get_all_costs() {
		$sql = "SELECT queue_cost_mem as memory, queue_cost_cpu as cpu, ";
		$sql .= "queue_cost_gpu as gpu, queue_cost_time_created as time ";
		$sql .= "FROM queue_cost ";
		$sql .= "WHERE queue_cost_queue_id='" . $this->get_queue_id() . "'";
		$sql .= "ORDER BY queue_cost_time_created ASC ";
		return $this->db->query($sql);

	}
	////////////////Private Functions//////////

	//load_by_id()
	//$id - integer - database id of queue.
	//loads queue object with id number
	private function load_by_id($id,$date) {
		$this->get_queue($id,$date);
	}
	//load_by_name()
	//$queue_name - string - name of queue
	//loads queue object with queue name
	private function load_by_name($queue_name,$date) {
		$sql = "SELECT queue_id FROM queues ";
		$sql .= "WHERE queue_name='" . $queue_name . "' LIMIT 1";
		$result = $this->db->query($sql);
		if ($result) {
			$this->get_queue($result[0]['queue_id'],$date);
		}
		else { return false;
		}
	}

	//get_queue()
	//gets queue information and assigns to variables
	private function get_queue($queue_id,$date) {

		$sql = "SELECT queues.*,queue_cost.* ";
		$sql .= "FROM queues ";
		$sql .= "LEFT JOIN queue_cost ON queue_cost.queue_cost_queue_id=queues.queue_id ";
		$sql .= "WHERE queues.queue_id='" . $queue_id . "' ";
		$sql .= "AND queue_cost_time_created<='" . $date . "' ";
		$sql .= "ORDER BY queue_cost.queue_cost_time_created DESC LIMIT 1";
		$result = $this->db->query($sql);
		if ($result) {
			$this->id = $result[0]['queue_id'];
			$this->name = $result[0]['queue_name'];
			$this->description = $result[0]['queue_description'];
			$this->ldap_group = $result[0]['queue_ldap_group'];
			$this->cpu_cost = $result[0]['queue_cost_cpu'];
			$this->mem_cost = $result[0]['queue_cost_mem'];
			$this->gpu_cost	= $result[0]['queue_cost_gpu'];
			$this->time_created = $result[0]['queue_cost_time_created'];
			$this->enabled = $result[0]['queue_enabled'];
			$this->queue_cost_id = $result[0]['queue_cost_id'];
		}
		else { return false;
		}
	}


	private function calculate_cpu_cost($cpu_time,$wallclock_time,$slots,$start_time,$end_time) {
		$final_time = $this->calculate_time($cpu_time,$wallclock_time,$slots,$start_time,$end_time);
		return $final_time * $this->get_cpu_cost();
	}

	private function calculate_mem_cost($wallclock_time,$mem,$start_time,$end_time) {
		//done to make multiply easy.  if a job was less than 0.5 second,
		//it gets rounded to 0 which then would cause the cost to be 0, so I increase it to 1.
		$final_time = 0;
		$elapsed_time = strtotime($end_time) - strtotime($start_time);
		if ($wallclock_time == 0) {
                        $wallclock_time = 1;
                }
		if ($elapsed_time == 0) {
                        $elapsed_time = 1;
                }
		if ($wallclock_time >= $elapsed_time) {
			$final_time = $wallclock_time;
		}
		elseif ($elapsed_time > $wallclock_time) {
			$final_time = $elapsed_time;
		}
		return $final_time * $mem * $this->convert_bytes_gb($this->get_mem_cost());

	}

	private function calculate_gpu_cost($wallclock_time,$num_gpu,$elapsed_time) {
		$final_time = 0;
		if ($wallclock_time == 0) {
                        $wallclock_time = 1;
                }
                if ($elapsed_time == 0) {
                        $elapsed_time = 1;
                }
                if ($wallclock_time >= $elapsed_time) {
                        $final_time = $wallclock_time;
                }
                elseif ($elapsed_time > $wallclock_time) {
                        $final_time = $elapsed_time;
                }
                return $final_time * $num_gpu * $this->get_gpu_cost();


	}

	private function calculate_time($cpu_time,$wallclock_time,$slots,$start_time,$end_time) {
		//There is a bug in torque where sometimes the cpu_time is way off by months and years.
		//bug_factor will be used to see if cpu_time is really large, if it is then do not use cpu_time.
		$bug_factor = 100;
		$final_time = 0;
		$elapsed_time = strtotime($end_time) - strtotime($start_time);

		//done to make multiply easy.  if a job was less than 0.5 second, it gets rounded to 0 which then would cause the cost to be 0, so I increase it to 1.
		if ($wallclock_time == 0) {
			$wallclock_time = 1;
		}
		if ($cpu_time == 0) {
			$cpu_time = 1;
		}
		if ($elapsed_time == 0) {
			$elapsed_time = 1;
		}
		$wallclock_total_time = $wallclock_time * $slots;
		$elapsed_total_time = $elapsed_time * $slots;

		if (($cpu_time >= $wallclock_total_time) && ($cpu_time >= $elapsed_total_time) && ($cpu_time < ($bug_factor * $elapsed_total_time)) 
			&& ($cpu_time < ($bug_factor * $wallclock_total_time))) {
			$final_time = $cpu_time;
		}
		elseif (($wallclock_total_time >= $cpu_time) && ($wallclock_total_time >= $elapsed_total_time)) {
			$final_time = $wallclock_total_time;
		}
		elseif (($elapsed_total_time > $cpu_time) && ($elapsed_total_time > $wallclock_total_time)) {
			$final_time = $elapsed_total_time;
		}
		return $final_time;
	}
	//convert_bytes_gb()
	//converts bytes to gigabytes
	//$bytes - bytes
	//returns gigabytes
	private function convert_bytes_gb($bytes) {
		return $bytes / 1073741824;

	}
}
