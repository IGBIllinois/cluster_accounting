<?php

class session {


	///////////////Private Variables//////////
	private $session_name;

        ////////////////Public Functions///////////

        public function __construct($session_name) {
		$this->session_name = $session_name;
		$this->set_settings();
		$this->start_session();		
        }

	public function __destruct() {}

	public function get_var($name) {
		$result = false;
		if ($this->is_session_started() && (isset($_SESSION[$name]))) {
			$result = $_SESSION[$name];
		}
		return $result;

	}

	public function get_all_vars() {
		return $_SESSION;

	}
	public function set_session($session_array) {
		foreach ($session_array as $key=>$var) {
			$this->set_session_var($key,$var);
		}

	}

	public function get_session_id() { 
		return session_id();
	}
	public function get_session_name() {
		return $this->session_name;
	}

	public function destroy_session() {
		if ($this->is_session_started()) {
			unset($_SESSION);
			session_destroy();
		}
	}

        public function set_session_var($name,$var) {
                $result = false;
                if ($this->is_session_started()) {
                        $_SESSION[$name] = $var;
                        $result = true;
                }
                return $result;

        }

	////////////////Private Functions/////////////

	private function start_session() {
                session_name($this->session_name);
                session_start();
        }
	private function is_session_started() {
		$result = false;
		if ($this->get_session_id() != "") {
			$result = true;
		}
		return $result;
	}


	private function set_settings() {
		$session_hash = "sha512";
		if (in_array($session_hash,hash_algos())) {
			ini_set("session.hash_function","sha512");
		}
		ini_set("session.entropy_file","/dev/urandom");
		ini_set("session.entropy_length","512");
		ini_set("session.hash_bits_per_character",6);
	}

}







?>
