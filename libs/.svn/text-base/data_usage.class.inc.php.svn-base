<?php
class data_usage {

        ////////////////Private Variables//////////
        private $db; //database object
        private $data_usage_id;
        private $time_created;
	private $bytes;
	private $files;

        ////////////////Public Functions///////////

        public function __construct($db,$id = 0) {
                $this->db = $db;

                if ($id != 0) {
                        $this->get_data_usage($id);
                }
        }
        public function __destruct() {
        }
	
	public function create($data_dir_id,$data_cost_dir,$bytes,$files) {
		$data_dir = new data_dir($this->db,$data_dir_id);
		$project = new project($this->db,$data_dir->get_project_id());
	
		$data_cost = new data_cost($this->db,0,$data_cost_dir);
		$total_cost = $data_cost->calculate_cost($bytes);
		if ($project->get_bill_project()) {
			$billed_cost = $data_cost->calculate_cost($bytes);
		}
		else { 
			$billed_cost = 0;
		}
		$insert_array = array('data_usage_data_dir_id'=>$data_dir_id,
				'data_usage_project_id'=>$project->get_project_id(),
				'data_usage_cfop_id'=>$project->get_cfop_id(),
				'data_usage_data_cost_id'=>$data_cost->get_data_cost_id(),
				'data_usage_total_cost'=>$total_cost,
				'data_usage_billed_cost'=>$billed_cost,
				'data_usage_bytes'=>$bytes,
				'data_usage_files'=>$files
				);
		return $this->db->build_insert('data_usage',$insert_array);

	}

	public function get_bytes() {
		return $bytes;
	}
	public function get_files() {
		return $files;
	}
	public function get_time_created() {
		return $time_created;
	}
	//////////////////Private Functions//////////

	private function get_data_usage($data_usage_id) {
		$sql = "SELECT data_usage.data_usage_id, data_usage.data_usage_bytes as bytes, ";
		$sql .= "data_usage.data_usage_files as files, data_usage.data_usage_time as time_created, ";
		$sql .= "data_dir.data_dir_path as path, projects.project_name as project ";
		$sql .= "FROM data_usage ";
		$sql .= "LEFT JOIN projects ON projects.project_id=data_usage.data_usage_project_id ";
		$sql .= "LEFT JOIN data_dir ON data_dir.data_dir_id=data_usage.data_usage_data_dir_id ";
		$sql .= "WHERE data_usage.data_usage_id='" . $data_usage_id . "' LIMIT 1";
		$this->db->query($sql);
		if ($result) {
			$this->data_usage_id = $result[0]['data_usage_id'];
			$this->bytes = $result[0]['bytes'];
			$this->files = $result[0]['files'];
			$this->time = $result[0]['time_created'];
			



		}



	}




}
?>
