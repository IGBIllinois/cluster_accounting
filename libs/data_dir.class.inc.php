<?php

class data_dir {


	private $db;
	private $id;
	private $project_id;
	private $directory;
	private $time_created;
	private $enabled;
	private $default;
	private $latest_size = 0;
	private $latest_size_date;

	private const PERCENTILE = 0.95;

	public function __construct($db,$data_dir_id = 0) {
		$this->db = $db;

		if ($data_dir_id != 0) {
			$this->id = $data_dir_id;
			$this->get_data_dir();
		}

	}

	public function __destruct() {
	}
	
	public function create($project_id,$directory,$default = 0) {
		$directory = $this->format_directory($directory);
		$this->project = new project($this->db,$project_id);

		$error = false;

		if ($this->data_dir_exists($directory)) {
			$error = true;
			$message .= "<div class='alert alert-danger'>Directory " . $directory . " is already in the database</div>";
		}

		if ($error) {
			return array('RESULT'=>false,"MESSAGE"=>$message);
		}
		else {
			$sql = "INSERT INTO data_dir(data_dir_project_id,data_dir_path,data_dir_default) ";
			$sql .= "VALUES(:project_id,:directory,:default)";
			$parameters = array(
				':project_id'=>$this->project->get_project_id(),
				':directory'=>$directory,
				':default'=>$default
			);
			$result = $this->db->insert_query($sql,$parameters);
			return array('RESULT'=>true,
					"data_dir_id"=>$result,
					"MESSAGE"=>"<div class='alert alert-success'>Directory " . $directory . " successfully added</div>"
			);
		}
	}
	
	public function get_data_dir_id() {
		return $this->id;
	}
	
	public function get_directory() {
		return $this->directory;
	}

	public function get_project_id() {
		return $this->project_id;
	}
	
	public function get_enabled() {
		return $this->enabled;
	}
	public function get_time_created() {
		return $this->time_created;
	}

	public function get_latest_size() {
		return $this->latest_size;
	}
	public function get_latest_size_date() {
		return $this->latest_size_date;
	}
	public function enable() {
                $sql = "UPDATE data_dir SET data_dir_enabled='1' ";
                $sql .= "WHERE data_dir_id=:data_dir_id LIMIT 1";
		$parameters = array(
			':data_dir_id'=>$this->get_data_dir_id()
		);
                $result = $this->db->non_select_query($sql,$parameters);
                if ($result) {
                        $this->enabled = 1;
                }
                return $result;


	}
	public function disable() {
		$error = false;
		$message = "";
		if (is_dir($this->get_directory())) {
                        $message = "Unable to delete directory.  Directory " . $this->get_directory() . " still exists.";
                        $error = true;
                }
		if (!$error) {
			$sql = "UPDATE data_dir SET data_dir_enabled='0' ";
			$sql .= "WHERE data_dir_id=:data_dir_id LIMIT 1";
			$parameters = array(
				':data_dir_id'=>$this->get_data_dir_id()
			);
			$result = $this->db->non_select_query($sql,$parameters);
			if ($result) {
				$this->enabled = 0;
				$message = "Successfully remove directory " . $this->get_directory() . ".";
			}
		}
		return array('RESULT'=>$result,'MESSAGE'=>$message);


	}
	
	private function get_data_dir() {
		$sql = "SELECT data_dir.*, ";
		$sql .= "ROUND(du.data_usage_bytes / :terabytes,3) as terabytes,du.data_usage_time as data_usage_time ";
		$sql .= "FROM data_dir ";
		$sql .= "LEFT JOIN (SELECT data_usage.data_usage_time, data_usage.data_usage_data_dir_id, data_usage.data_usage_bytes ";
		$sql .= "FROM data_usage WHERE data_usage.data_usage_data_dir_id=:data_dir_id ORDER BY data_usage.data_usage_time DESC LIMIT 1) as du ";
                $sql .= "ON du.data_usage_data_dir_id=data_dir.data_dir_id ";
		$sql .= "WHERE data_dir_id=:data_dir_id ";
		$sql .= "LIMIT 1";
		$parameters = array(
			':data_dir_id'=>$this->get_data_dir_id(),
			':terabytes'=>data_functions::CONVERT_TERABYTES
		);
		$result = $this->db->query($sql,$parameters);
		if ($result) {
			$this->directory = $result[0]['data_dir_path'];
			$this->time_created = $result[0]['data_dir_time'];
			$this->project_id = $result[0]['data_dir_project_id'];
			$this->enabled = $result[0]['data_dir_enabled'];
			$this->default = $result[0]['data_dir_default'];
			$this->latest_size = $result[0]['terabytes'];
			$this->latest_size_date = $result[0]['data_usage_time'];
			return true;
		}
		return false;
	}

	private function format_directory($directory) {
		if (strrpos($directory,"/") == strlen($directory) -1) {
			return substr($directory,0,strlen($directory)-1);
		}
		else {
			return $directory;
		}

	}

	public function is_default() {
		return $this->default;
	}
	public function directory_exists() {
		return is_dir($this->get_directory());

	}
	
	private function data_dir_exists($directory) {
		$sql = "SELECT count(1) as count FROM data_dir ";
		$sql .= "WHERE data_dir_path LIKE ':data_dir_path%' ";
		$sql .= "AND data_dir_enabled='1'";
		
		$parameters = array(
			':data_dir_path'=>$directory
		);
		$result = $this->db->query($sql,$parameters);

		if ($result[0]['count']) {
			return true;
		}
		return false;

	}
	
	private function check_sub_dir($directory) {
		$directory = substr($directory,1,strlen($directory));
		$directories = explode("/",$directory);

		for ($i=0; $i < count($directories); $i++) {
			$sub_dir = "";

			for ($j=0; $j<=$i; $j++) {
				$sub_dir .= "/" . $directories[$j];
			}
			if($this->data_dir_exists($sub_dir)) {
				return true;
			}
		}
		return false;

	}
	
	public function add_usage($bytes,$files=0) {

                $project = new project($this->db,$this->get_project_id());

                if ($project->get_bill_project()) {
                }
                else {
                }
                $insert_array = array('data_usage_data_dir_id'=>$this->get_data_dir_id(),
                                'data_usage_project_id'=>$project->get_project_id(),
                                'data_usage_cfop_id'=>$project->get_cfop_id(),
                                'data_usage_bytes'=>$bytes
                                );
                $insert_id = $this->db->build_insert('data_usage',$insert_array);
		if ($insert_id) {
			return array('RESULT'=>true,'INSERT_ID'=>$insert_id);

		}
		else {
			return array('RESULT'=>false);
		}

	}	

	public function get_usage($month,$year) {
		$sql = "SELECT * FROM data_usage ";
		$sql .= "LEFT JOIN data_dir ON data_dir_id=data_usage_data_dir_id ";
		$sql .= "WHERE MONTH(data_usage_time)=:month ";
		$sql .= "AND YEAR(data_usage_time)=:year ";
		$sql .= "AND data_usage_data_dir_id=:data_dir_id ";
		$sql .= "ORDER BY data_usage_bytes DESC";
		$parameters = array(
			':data_dir_id'=>$this->get_data_dir_id(),
			':month'=>$month,
			':year'=>$year
		);
		$result = $this->db->query($sql,$parameters);
		$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
		if (count($result) < $days_in_month) {
			$diff = $days_in_month - count($result);
			$empty_array = array();
			for ($i=0;$i<$diff;$i++) {
				array_push($empty_array,array('data_usage_bytes'=>0));
			}
			$result = array_merge($empty_array,$result);	
		}
		$slice = round(count($result)*self::PERCENTILE,0,PHP_ROUND_HALF_DOWN);
		return array_slice($result,0,$slice);
	}

	public function get_usage_range($start_date,$end_date) {
                $sql = "SELECT ROUND(data_usage_bytes / :terabytes,3) as terabytes, ";
		$sql .= "DATE_FORMAT(data_usage_time,'%Y-%m-%d') as date FROM data_usage ";
                $sql .= "LEFT JOIN data_dir ON data_dir_id=data_usage_data_dir_id ";
                $sql .= "WHERE data_usage_time BETWEEN :start_date AND :end_date ";
                $sql .= "AND data_usage_data_dir_id=:data_dir_id ";
                $sql .= "ORDER BY data_usage_time ASC";
                $parameters = array(
                        ':data_dir_id'=>$this->get_data_dir_id(),
                        ':start_date'=>$start_date->format("Y-m-d H:i:s"),
                        ':end_date'=>$end_date->format("Y-m-d H:i:s"),
			':terabytes'=>data_functions::CONVERT_TERABYTES
                );
                return $this->db->query($sql,$parameters);
        }

	public function add_data_bill($month,$year,$bytes) {
		$bill_date = $year . "-" . $month . "-01 00:00:00";
		$sql = "SELECT count(1) as count ";
		$sql .= "FROM data_bill ";
		$sql .= "WHERE data_bill.data_bill_date=:bill_date ";
		$sql .= "AND data_bill_data_dir_id=:data_dir_id ";
		$sql .= "LIMIT 1";
		$parameters = array(
			':data_dir_id'=>$this->get_data_dir_id(),
			':bill_date='=>$bill_date
		);
		$check_exists = $this->db->query($sql,$parameters);
		$result = true;
		$insert_id = 0;
		if ($check_exists[0]['count']) {
			$result = false;
			$message = "Data Bill: Directory: " . $this->get_directory() . " Bill already calculated";
		}
		else {
	                $project = new project($this->db,$this->get_project_id());
			$data_cost_result = data_functions::get_current_data_cost_by_type($this->db,'standard');
        	        $data_cost = new data_cost($this->db,$data_cost_result['id']);
			$total_cost = $data_cost->calculate_cost($bytes);
			$billed_cost = 0;
			if ($project->get_bill_project()) {
				$billed_cost = $total_cost;
			}
        	        $insert_array = array('data_bill_data_dir_id'=>$this->get_data_dir_id(),
                	                'data_bill_project_id'=>$project->get_project_id(),
                        	        'data_bill_cfop_id'=>$project->get_cfop_id(),
                                	'data_bill_data_cost_id'=>$data_cost_result['id'],
	                                'data_bill_avg_bytes'=>$bytes,
					'data_bill_total_cost'=>$total_cost,
					'data_bill_billed_cost'=>$billed_cost,
					'data_bill_date'=>$bill_date
	                                );
        	        $insert_id = $this->db->build_insert('data_bill',$insert_array);
			$message = "Data Bill: Directory: " . $this->get_directory() . " Successfully added data bill";
		}
		return array('RESULT'=>$result,'MESSAGE'=>$message,'id'=>$insert_id);
	}

}

?>
