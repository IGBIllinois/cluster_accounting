<?php
class ldap {

        ///////////////Private Variables//////////
        private $ldap_resource = false;
        private $ldap_host;
        private $ldap_base_dn;
        private $ldap_bind_user;
        private $ldap_bind_pass;
        private $ldap_ssl = false;
        private $ldap_port = 389;
        private $ldap_protocol = 3;    
        ////////////////Public Functions///////////

        public function __construct($host,$ssl,$port,$base_dn) {
                $this->set_host($host);
                $this->set_ssl($ssl);
                $this->set_port($port);
		$this->set_base_dn($base_dn);
                $this->connect();
        }


        public function __destruct() {}

        //get ldap functions
        public function get_host() { return $this->ldap_host; }
        public function get_base_dn() { return $this->ldap_base_dn; }
        public function get_bind_user() { return $this->ldap_bind_user; }
        public function get_ssl() { return $this->ldap_ssl; }
        public function get_port() { return $this->ldap_port; }
        public function get_protocol() { return $this->ldap_protocol; }
        public function get_resource() { return $this->ldap_resource; }
	public function get_connection() {
			return is_resource($this->ldap_resource);
	}
        //set ldap functions
        public function set_protocol($ldap_protocol) {
                $this->ldap_protocol = $ldap_protocol;
                ldap_set_option($this->get_resource(),LDAP_OPT_PROTOCOL_VERSION,$ldap_protocol);
        }


	//bind()
	//binds to the ldap server as specified user.  If no username/password is provide, binds anonymously
	//$rdn - full rdn of user
	//$password - password
	//returns true if successful, false otherwise.
        public function bind($rdn = "",$password = "") {
		$result = false;
		if ($this->get_connection()) {
			if (($rdn != "") && ($password != "")) {
				$result = @ldap_bind($this->get_resource(), $rdn, $password);

			}
			elseif (($rdn == "") && ($password == "")) {
				$result = @ldap_bind($this->get_resource());
			}
		}
		return $result;

        }



        public function search($filter,$ou = "",$attributes = "") {
		$result = false;
		if ($ou == "") {
			$ou = $this->get_base_dn();
		}
		if (($this->get_connection()) && ($attributes != "")) {
	                $ldap_result = ldap_search($this->get_resource(),$ou,$filter,$attributes);
	                $result = ldap_get_entries($this->get_resource(),$ldap_result);
		}
		elseif (($this->get_connection()) && ($attributes == "")) {
			$ldap_result = ldap_search($this->get_resource(),$ou,$filter);
                        $result = ldap_get_entries($this->get_resource(),$ldap_result);

		}
		return $result;

        }


	public function replace($rdn,$entries) {
		if(ldap_mod_replace($this->get_resource(), $rdn, $entries))
                {
                        return true;
                }
                else{
                        return false;
                }
	}

	public function is_ldap_user($username) {
                $username = trim(rtrim($username));
                $filter = "(uid=" . $username . ")";
                $attributes = array('');
                $result = $this->search($filter,"",$attributes);
                if ($result['count']) {
                        return true;
                }
                else {
                        return false;
                }


        }

        public function get_group_members($group) {
                if ($this->get_connection()) {
                        $group = trim(rtrim($group));
                        $filter = "(cn=" . $group . ")";
                        $attributes = array('memberUid');
                        $result = $this->search($filter,"",$attributes);
                        unset($result[0]['memberuid']['count']);
                        $members = array();
                        foreach ($result[0]['memberuid'] as $row) {
                                array_push($members,$row);
                        }
                        return $members;
                }
        }

        public function is_group_member($username,$group) {
                $group_members = $this->get_group_members($group);
                return in_array($username,$group_members);

        }
	 public function get_user_groups($username) {
                if ($this->get_connection()) {
                        $username = trim(rtrim($username));
                        $filter = "(&(cn=*)(memberUid=" . $username . "))";
                        $attributes = array('cn');
                        $result = $this->search($filter,"",$attributes);
                        unset($result['count']);
                        $groups = array();
                        foreach ($result as $row) {
                                array_push($groups,$row['cn'][0]);
                        }
                        return $groups;
                }


        }
        public function get_group_exists($group) {
                if ($this->get_connection()) {
                        $group = trim(rtrim($group));
                        $filter = "(cn=" . $group . ")";
                        $attributes = array('');
                        $result = $this->search($filter,"",$attributes);
                        if ($result['count']) {
                                return true;
                        }
                        return false;
                }

        }

	public function get_home_dir($username) {
                if ($this->get_connection()) {
                        $username = trim(rtrim($username));
                        $filter = "(uid=" . $username . ")";
                        $attributes = array('homeDirectory');
                        $result = $this->search($filter,"",$attributes);
                        if ($result['count']) {

                                return $result[0]['homedirectory'][0];
                        }
                        else {
                                return false;
                        }
                }


        }

        public function get_email($username) {
                if ($this->get_connection()) {
                        $username = trim(rtrim($username));
                        $filter = "(uid=" . $username . ")";
                        $attributes = array('mail');
                        $result = $this->search($filter,"",$attributes);
                        if (($result['count']) && (isset($result[0]['mail'][0]))){
				return $result[0]['mail'][0];
			
                        }
                        else {
                                return false;
                        }
                }


        }
	
	public function get_all_users() {
		$users_array = array();
		if ($this->get_connection()) {
                        $filter = "(objectClass=posixAccount)";
                        $attributes = array('uid');
                        $result = $this->search($filter,"",$attributes);
			foreach ($result as $user) {
				array_push($users_array,$user['uid'][0]);

			}
				
                }
		return $users_array;


	}

        public function get_ldap_full_name($username) {
                if ($this->get_connection()) {
                        $username = trim(rtrim($username));
                        $filter = "(uid=" . $username . ")";
                        $attributes = array("cn");
                        $result = $this->search($filter,"",$attributes);
                        return $result[0]['cn'][0];
                }
                else { return false;
                }
        }

	//////////////////Private Functions/////////////////////

	private function set_host($ldap_host) { $this->ldap_host = $ldap_host; }
        private function set_base_dn($ldap_base_dn) { $this->ldap_base_dn = $ldap_base_dn; }
        private function set_ssl($ldap_ssl) { $this->ldap_ssl = $ldap_ssl; }
        private function set_port($ldap_port) { $this->ldap_port = $ldap_port; }

	private function connect() {
                $ldap_uri;
                if ($this->get_ssl()) {
                        $ldap_uri = "ldaps://" . $this->get_host() . ":" . $this->get_port();
                }
                elseif (!$this->get_ssl()) {
                        $ldap_uri = "ldap://" . $this->get_host() . ":" . $this->get_port();
                }
		$this->ldap_resource = ldap_connect($ldap_uri);
		if ($this->get_connection()) {
			return true;
		}
		return false;
        }

}

?>

