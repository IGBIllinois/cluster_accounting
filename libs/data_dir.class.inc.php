<?php

/**
 *
 * @author dslater
 *
 */
class data_dir {


	/**
	 * @var unknown
	 */
	private $db;

	/**
	 * @var unknown
	 */
	private $id;

	/**
	 * @var unknown
	 */
	private $project_id;

	/**
	 * @var unknown
	 */
	private $directory;

	/**
	 *
	 * @var unknown
	 */
	private $time_created;


	private $enabled;
	private $default;

	/**
	 * @param db object $db
	 * @param int $data_dir_id
	 */
	public function __construct($db,$data_dir_id = 0) {
		$this->db = $db;

		if ($data_dir_id != 0) {
			$this->id = $data_dir_id;
			$this->get_data_dir();
		}

	}

	/**
	 *
	 */
	public function __destruct() {
	}
	/**
	 *
	 * @param unknown $project_id
	 * @param unknown $directory
	 * @return multitype:boolean string |multitype:boolean string unknown
	 */
	public function create($project_id,$directory,$default = 0) {
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
			$sql = "INSERT INTO data_dir(data_dir_project_id,data_dir_path,data_dir_default) ";
			$sql .= "VALUES('" . $this->project->get_project_id() . "','" . $directory . "','" . $default . "')";
			$result = $this->db->insert_query($sql);
			return array('RESULT'=>true,
					"data_dir_id"=>$result,
					"MESSAGE"=>"<div class='alert alert-success'>Directory " . $directory . " successfully added</div>"
			);
		}
	}
	/**
	 * 
	 * @return int
	 */
	public function get_data_dir_id() {
		return $this->id;
	}
	/**
	 * 
	 */
	public function get_directory() {
		return $this->directory;
	}

	public function get_project_id() {
		return $this->project_id;
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
	/**
	 * 
	 * @return boolean
	 */
	private function get_data_dir() {
		$sql = "SELECT * FROM data_dir WHERE data_dir_id='" . $this->id . "' LIMIT 1";
		$result = $this->db->query($sql);
		if ($result) {
			$this->directory = $result[0]['data_dir_path'];
			$this->time_created = $result[0]['data_dir_time'];
			$this->project_id = $result[0]['data_dir_project_id'];
			$this->enabled = $result[0]['data_dir_enabled'];
			$this->default = $result[0]['data_dir_default'];
			return true;
		}
		return false;
	}

	/**
	 * 
	 * @param unknown $directory
	 * @return unknown
	 */
	private function format_directory($directory) {
		if (strrpos($directory,"/") == strlen($directory) -1) {
			return substr($directory,0,strlen($directory)-1);
		}
		else {
			return $directory;
		}

	}

	/**
	 * 
	 * @param unknown $directory
	 */
	public function directory_exists() {
		return is_dir($this->get_directory());

	}
	/**
	 * 
	 * @param unknown $directory
	 * @return boolean
	 */
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
	/**
	 * 
	 * @param unknown $directory
	 * @return boolean
	 */
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
	
	
}
