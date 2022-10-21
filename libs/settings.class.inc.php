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
	private const TIMEZONE = "UTC";
	private const SMTP_PORT = 25;
	private const SMTP_HOST = "localhost";

	public static function get_version() {
                return VERSION;
        }

        public static function get_title() {
                return TITLE;
        }

	public static function get_log_enabled() {
		if (defined("ENABLE_LOG") && (is_bool(ENABLE_LOG))) {
			return ENABLE_LOG;
		}
		return self::ENABLE_LOG;
	}

	public static function get_logfile() {
                if (self::get_log_enabled() && !file_exists(LOG_FILE)) {
                        touch(LOG_FILE);
                }
                return LOG_FILE;

        }

	public static function get_timezone() {
		if (defined("TIMEZONE") && (TIMEZONE != "")) {
			return TIMEZONE;
		}
		return self::TIMEZONE;
	}
	public static function get_ldap_host() {
		if (defined("LDAP_HOST")) {
			return LDAP_HOST;
		}
		return self::LDAP_HOST;
	}

	public static function get_ldap_port() {
		if (defined("LDAP_PORT")) {
			return LDAP_PORT;
		}
		return self::LDAP_PORT;
	}
	public static function get_ldap_base_dn() {
		if (defined("LDAP_BASE_DN")) {
			return LDAP_BASE_DN;
		}
		return self::LDAP_BASE_DN;
	}
	public static function get_ldap_ssl() {
		if (defined("LDAP_SSL")) {
			return LDAP_SSL;
		}
		return self::LDAP_SSL;
	}

	public static function get_ldap_tls() {
		if (defined("LDAP_TLS")) {
			return LDAP_TLS;
		}
		return self::LDAP_TLS;
	}
	public static function get_ldap_bind_user() {
		if (defined("LDAP_BIND_USER")) {
			return LDAP_BIND_USER;
		}
		return self::LDAP_BIND_USER;
	}
	public static function get_ldap_bind_password() {
		if (defined("LDAP_BIND_PASS")) {
			return LDAP_BIND_PASS;
		}
		return self::LDAP_BIND_PASS;
	}

	public static function get_ldap_group() {
		if (defined("LDAP_GROUP")) {
			return LDAP_GROUP;
		}
		return false;
	
	}

	public static function get_session_name() {
		if (defined("SESSION_NAME")) {
			return SESSION_NAME;
		}
		return self::SESSION_NAME;
	}
	public static function get_session_timeout() {
		if (defined("SESSION_TIMEOUT")) {
			return SESSION_TIMEOUT;
		}
		return self::SESSION_TIMEOUT;
	}

	public static function get_mysql_host() {
		if (defined("MYSQL_HOST")) {
			return MYSQL_HOST;
		}
		return self::MYSQL_HOST;

	}

	public static function get_mysql_user() {
		if (defined("MYSQL_USER")) {
			return MYSQL_USER;
		}
		return false;
	}

	public static function get_mysql_password() {
		if (defined("MYSQL_PASSWORD")) {
			return MYSQL_PASSWORD;
		}
		return false;
	}
	public static function get_mysql_port() {
		if (defined("MYSQL_PORT")) {
			return MYSQL_PORT;
		}
		return self::MYSQL_PORT;

	}

	public static function get_mysql_database() {
		if (defined("MYSQL_DATABASE")) {
			return MYSQL_DATABASE;
		}
		return false;
	}

	public static function get_mysql_ssl() {
		if (defined("MYSQL_SSL")) {
			return MYSQL_SSL;
		}
		return self::MYSQL_SSL;

	}
	public static function get_reserve_processor_factor() {
		return RESERVE_PROCESSORS_FACTOR;

	}
	
	public static function get_reserve_memory_factor() {
		return RESERVE_MEMORY_FACTOR;
	}
	public static function get_torque_job_dir() {
		return TORQUE_JOBS_LOG;

	}
	
	public static function get_admin_email() {
		return ADMIN_EMAIL;
	}
	
	public static function get_torque_accounting_dir() {
		return TORQUE_ACCOUNTING;
	}
	public static function get_torque_job_logs_dir() {
		return TORQUE_JOBS_LOG;
	}


	public static function get_job_scheduler() {
		return JOB_SCHEDULER;
	}
	public static function get_server_name() {
                $server_name = substr($_SERVER['SERVER_NAME'],0,strpos($_SERVER['SERVER_NAME'],"."));
                return $server_name;

	}
	
	public static function get_root_data_dirs() {
		$data_dirs = explode(" ",ROOT_DATA_DIR);
		return $data_dirs;
		
		
	}

	public static function get_boa_cfop() {
		return BOA_CFOP;
		

	}

	public static function get_password_reset_url() {
		if (defined('PASSWORD_RESET_URL') && (PASSWORD_RESET_URL != "") &&
			filter_var(PASSWORD_RESET_URL,FILTER_VALIDATE_URL,FILTER_FLAG_SCHEME_REQUIRED)) {

			return PASSWORD_RESET_URL;
		}
		return false;
	}

	public static function get_twig_dir() {
		$dir = dirname(__DIR__) . "/" . TWIG_DIR;
		return $dir;
	}
	
	public static function get_email_css() {
			
		$file_path = dirname(__DIR__) . "/" . EMAIL_CSS;
		return $file_path;
	}

	public static function get_email_css_contents() {
		$css = self::get_email_css();
		return file_get_contents($css);

	}

	public static function get_website_url() {
		return WEBSITE_URL;
	}

	public static function get_smtp_host() {
		if (defined("SMTP_HOST")) {
			return SMTP_HOST;
		}
		return self::SMTP_HOST;

	}
	public static function get_smtp_port() {
		if (defined("SMTP_PORT")) {
			return SMTP_PORT;
		}
		return self::SMTP_PORT;

	}

	public static function get_smtp_username() {
                if (defined("SMTP_USERNAME")) {
                        return SMTP_USERNAME;
                }
                return false;
        }

        public static function get_smtp_password() {
                if (defined("SMTP_PASSWORD")) {
                        return SMTP_PASSWORD;
                }
                return false;

        }
}

?>
