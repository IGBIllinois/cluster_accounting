<?php

class data_cost {
	
	////////////////Private Variables//////////
	private $db; //database object
	private $id;
	private $type;
	private $cost;
	private $time_created;
	private $enabled;

	const bytes_to_terabytes = 1099511627776;	

	////////////////Public Functions///////////
	
	public function __construct($db,$id = 0) {
		$this->db = $db;
		
		if ($id != 0) {
			$this->get_data_cost($id);
		}
	}
	public function __destruct() {
	}
	
	public function get_data_cost_id() {
		return $this->id;
	}
	public function get_type() {
		return $this->type;
	}
	public function get_cost() {
		return $this->cost;
	}
	public function get_formatted_cost() {
		return number_format($this->get_cost(),2);
	}
	public function get_time_created() {
		return $this->time_created;
	}
	public function get_enabled() {
		return $this->enabled;
	}

	public function update_cost($cost) {
		$insert_array = array('data_cost_type'=>$this->get_type(),
				'data_cost_value'=>$cost);
		$result = $this->db->build_insert("data_cost",$insert_array);
		if ($result) {
			$this->disable();
			$message = "<div class='alert alert-success'>Cost successfully updated.</div>";
			return array('RESULT'=>true,'ID'=>$result,'MESSAGE'=>$message);
			
		}
	}
	public function enable() {
		$sql = "UPDATE data_cost SET data_cost_enabled='1' ";
		$sql .= "WHERE data_cost_id='" . $this->get_data_cost_id() . "' LIMIT 1";
		$result = $this->db->non_select_query($sql);
		if ($result) {
			$this->enabled = 1;
		}
		return $result;
	}
	public function disable() {
		$sql = "UPDATE data_cost SET data_cost_enabled='0' ";
		$sql .= "WHERE data_cost_id='" . $this->get_data_cost_id() . "' LIMIT 1";
		$result = $this->db->non_select_query($sql);
		if ($result) {
			$this->enabled = 0;
		}
		return $result;
	
	}

	public function calculate_cost($bytes) {
		$terabytes = $this->convert_terabytes($bytes);
		return $terabytes * $this->get_cost();

	}	
	/////////////////Private Functions///////////
	
	private function get_data_cost($data_cost_id) {
		$sql = "SELECT * FROM data_cost ";
		$sql .= "WHERE data_cost_id='" . $data_cost_id . "' LIMIT 1";
		$result = $this->db->query($sql);
		if ($result) {
			$this->id = $result[0]['data_cost_id'];
			$this->type = $result[0]['data_cost_type'];
			$this->cost = $result[0]['data_cost_value'];
			$this->time_created = $result[0]['data_cost_time'];
			$this->enabled = $result[0]['data_cost_enabled'];
			
		}
		
	}
	
	private function convert_terabytes($bytes) {
		return $bytes / self::bytes_to_terabytes;
	}
}
