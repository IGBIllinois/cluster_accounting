<?php

class settings {

	private const ENABLE_LOG = false;
	private const LDAP_HOST = "localhost";
	private const LDAP_PORT = 389;
	private const LDAP_BASE_DN = "";
	private const LDAP_SSL = false;
	private const LDAP_TLS = false;
	private const LDAP_BIND_USER = "";
	private const LDAP_BIND_PASS = "";
	private const SESSION_TIMEOUT = 300;
	private const SESSION_NAME = "PHPSESSID";
	private const MYSQL_HOST = "localhost";
	private const MYSQL_PORT = 3306;
	private const MYSQL_SSL = false;

	public static function get_reserve_processor_factor() {
		return __RESERVE_PROCESSORS_FACTOR__;

	}
	
	public static function get_reserve_memory_factor() {
		return __RESERVE_MEMORY_FACTOR__;
	}
	public static function get_torque_job_dir() {
		return __TORQUE_JOBS_LOG__;

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

	public static function get_job_scheduler() {
		return __JOB_SCHEDULER__;
	}
	public static function get_server_name() {
                $server_name = substr($_SERVER['SERVER_NAME'],0,strpos($_SERVER['SERVER_NAME'],"."));
                return $server_name;

	}
	
	public static function get_root_data_dirs() {
		$data_dirs = explode(" ",__ROOT_DATA_DIR__);
		return $data_dirs;
		
		
	}

	public static function get_boa_cfop() {
		return __BOA_CFOP__;
		

	}

	public static function get_password_reset_url() {
		if (defined('__PASSWORD_RESET_URL__') && (__PASSWORD_RESET_URL__ != "") &&
			filter_var(__PASSWORD_RESET_URL__,FILTER_VALIDATE_URL,FILTER_FLAG_SCHEME_REQUIRED)) {

			return __PASSWORD_RESET_URL__;
		}
		return false;
	}

	public static function get_twig_dir() {
		$dir = dirname(__DIR__) . "/" . __TWIG_DIR__;
		return $dir;
	}
	
	public static function get_email_css() {
			
		$file_path = dirname(__DIR__) . "/" . __EMAIL_CSS__;
		return $file_path;
	}

	public static function get_email_css_contents() {
		$css = self::get_email_css();
		$contents = file_get_contents($css); 
		return file_get_contents($css);

	}

	public static function get_website_url() {
		return WEBSITE_URL;
	}
}

?>
