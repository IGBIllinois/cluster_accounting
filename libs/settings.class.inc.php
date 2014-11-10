<?php

class settings {


	public static function get_reserve_processor_factor() {
		return __RESERVE_PROCESSORS_FACTOR__;

	}
	
	public static function get_reserve_memory_factor() {
		return __RESERVE_MEMORY_FACTOR__;
	}
	public static function get_torque_job_dir() {
		return __TORQUE_JOBS_LOG__;

	}
	
	public static function get_galaxy_user() {
		return __GALAXY_USER__;
	}
	public static function get_admin_email() {
		return __ADMIN_EMAIL__;
	}
	public static function get_session_name() {
		return __SESSION_NAME__;
	}

	public static function get_session_timeout() {
		return __SESSION_TIMEOUT__;
	}
	
	public static function get_torque_accounting_dir() {
		return __TORQUE_ACCOUNTING__;
	}
	public static function get_torque_job_logs_dir() {
		return __TORQUE_JOBS_LOG__;
	}

	public static function get_version() {
		return __VERSION__;
	}

	public static function get_title() {
		return __TITLE__; 
	}

	public static function get_server_name() {
                $server_name = substr($_SERVER['SERVER_NAME'],0,strpos($_SERVER['SERVER_NAME'],"."));
                return $server_name;

	}
	
	public static function get_root_data_dirs() {
		$data_dirs = explode(" ",__ROOT_DATA_DIR__);
		return $data_dirs;
		
		
	}
}

?>
