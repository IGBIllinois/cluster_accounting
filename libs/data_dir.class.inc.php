<?php

class data_dir {


	private $db;
	private $id;
	private $project_id;
	private $directory;
	private $time_created;
	private $enabled;
	private $default;
	private $data_cost_id;
	private $cost_type;


	public function __construct($db,$data_dir_id = 0) {
		$this->db = $db;

		if ($data_dir_id != 0) {
			$this->id = $data_dir_id;
			$this->get_data_dir();
		}

	}

	public function __destruct() {
	}
	
	public function create($project_id,$directory,$default = 0,$data_cost_id=5) {
		$directory = $this->format_directory($directory);
		$this->project = new project($this->db,$project_id);

		$error = false;

		if ($this->data_dir_exists($directory)) {
			$error = true;
			$message .= "<div class='alert'>Directory " . $directory . " is already in the database</div>";
		}

		if ($error) {
			return array('RESULT'=>false,"MESSAGE"=>$message);
		}
		else {
			$sql = "INSERT INTO data_dir(data_dir_project_id,data_dir_path,data_dir_default,data_dir_data_cost_id) ";
			$sql .= "VALUES('" . $this->project->get_project_id() . "','" . $directory . "'";
			$sql .= ",'" . $default . "','" . $data_cost_id . "')";
			$result = $this->db->insert_query($sql);
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
	
	public function get_data_cost_id() {
		return $this->data_cost_id;
	}
	public function get_cost_type() {
		return $this->cost_type;
	}
	public function enable() {
                $sql = "UPDATE data_dir SET data_dir_enabled='1' ";
                $sql .= "WHERE data_dir_id='" . $this->get_data_dir_id() . "' LIMIT 1";
                $result = $this->db->non_select_query($sql);
                if ($result) {
                        $this->enabled = 1;
                }
                return $result;


	}
	public function disable() {
		$sql = "UPDATE data_dir SET data_dir_enabled='0' ";
		$sql .= "WHERE data_dir_id='" . $this->get_data_dir_id() . "' LIMIT 1";
		$result = $this->db->non_select_query($sql);
		if ($result) {
			$this->enabled = 0;
		}
		return $result;


	}
	
	private function get_data_dir() {
		$sql = "SELECT * FROM data_dir ";
		$sql .= "LEFT JOIN data_cost ON data_cost.data_cost_id=data_dir.data_dir_data_cost_id ";
		$sql .= "WHERE data_dir_id='" . $this->id . "' ";
		$sql .= "LIMIT 1";
		$result = $this->db->query($sql);
		if ($result) {
			$this->directory = $result[0]['data_dir_path'];
			$this->time_created = $result[0]['data_dir_time'];
			$this->project_id = $result[0]['data_dir_project_id'];
			$this->enabled = $result[0]['data_dir_enabled'];
			$this->default = $result[0]['data_dir_default'];
			$this->data_cost_id = $result[0]['data_dir_data_cost_id'];
			$this->cost_type = $result[0]['data_cost_type'];
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

	public function directory_exists() {
		return is_dir($this->get_directory());

	}
        public function get_dir_size() {

                $result = false;
                if (is_dir($this->get_directory())) {
                        $exec = "ls -ld " . $this->get_directory() . " | awk '{print $5}'";
                        $exit_status = 1;
                        $output_array = array();
                        $output = exec($exec,$output_array,$exit_status);
                        if (!$exit_status) {
                                $result = $output;
                        }

                }
		else {
			$result = 0;
		}
                return $result;
        }
	
	private function data_dir_exists($directory) {
		$sql = "SELECT count(1) as count FROM data_dir ";
		$sql .= "WHERE data_dir_path LIKE '" . $directory . "%' ";
		$sql .= "AND data_dir_enabled='1'";
		$result = $this->db->query($sql);

		if ($result[0]['count']) {
			return true;
		}
		else { return false;
		}

	}
	
	private function check_sub_dir($directory) {
		$directory = substr($directory,1,strlen($directory));
		$directories = explode("/",$directory);

		for ($i=0; $i < count($directories); $i++) {
			$sub_dir = "";

			for ($j=0; $j<=$i; $j++) {
				$sub_dir .= "/" . $directories[$j];
				echo $sub_dir;
			}
			if($this->data_dir_exists($sub_dir)) {
				return true;
			}
		}
		return false;

	}
	
	public function add_usage($bytes,$files=0) {

                $project = new project($this->db,$this->get_project_id());

                $data_cost = new data_cost($this->db,$this->get_data_cost_id());
                if ($project->get_bill_project()) {
                }
                else {
                }
                $insert_array = array('data_usage_data_dir_id'=>$this->get_data_dir_id(),
                                'data_usage_project_id'=>$project->get_project_id(),
                                'data_usage_cfop_id'=>$project->get_cfop_id(),
                                'data_usage_data_cost_id'=>$this->get_data_cost_id(),
                                'data_usage_bytes'=>$bytes,
                                'data_usage_files'=>$files
                                );
                return $this->db->build_insert('data_usage',$insert_array);


	}	

}
