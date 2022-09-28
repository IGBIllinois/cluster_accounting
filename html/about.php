<?php
require_once 'includes/main.inc.php';
require_once 'includes/header.inc.php';

?>
<table class='table table-bordered table-sm'>
		<tbody>
		<tr><td>Code Website</td></td><td><a href='<?php echo settings::get_website_url(); ?>' target='_blank'><?php echo settings::get_website_url(); ?></a></td></tr>
		<tr><td>App Version</td><td><?php echo settings::get_version(); ?></td></tr>
		<tr><td>Webserver Version</td><td><?php echo \IGBIllinois\Helper\functions::get_webserver_version(); ?></td></tr>
		<tr><td>MySQL Version</td><td><?php echo $db->get_version(); ?></td>
		<tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
		<tr><td>PHP Extensions</td><td><?php 
			$extensions_string = "";
			foreach (\IGBIllinois\Helper\functions::get_php_extensions() as $row) {
				$extensions_string .= implode(", ",$row) . "<br>";
			}
			echo $extensions_string;
		?></td></tr>
		</tbody>
	</table>

	<table class='table table-bordered table-sm'>
	<thead>
		<tr><th>Setting</th><th>Value</th></tr>
	</thead>
	<tbody>
		<tr><td>ENABLE_LOG</td><td><?php if (settings::get_log_enabled()) { echo "TRUE"; } else { echo "FALSE"; } ?></td></tr>
		<tr><td>LOG_FILE</td><td><?php echo settings::get_logfile(); ?></td></tr>
		<tr><td>TIMEZONE</td><td><?php echo settings::get_timezone(); ?></td></tr>
		<tr><td>LDAP_HOST</td><td><?php echo settings::get_ldap_host(); ?></td></tr>
		<tr><td>LDAP_BASE_DN</td><td><?php echo settings::get_ldap_base_dn(); ?></td></tr>
		<tr><td>LDAP_GROUP</td><td><?php echo settings::get_ldap_group(); ?></td></tr>	
		<tr><td>LDAP_SSL</td><td><?php if (settings::get_ldap_ssl()) { echo "TRUE"; } else { echo "FALSE"; } ?></td></tr>
		<tr><td>LDAP_TLS</td><td><?php if (settings::get_ldap_tls()) { echo "TRUE"; } else { echo "FALSE"; } ?></td></tr>
		<tr><td>LDAP_PORT</td><td><?php echo settings::get_ldap_port(); ?></td></tr>
		<tr><td>MYSQL_HOST</td><td><?php echo MYSQL_HOST; ?></td></tr>
		<tr><td>MYSQL_DATABASE</td><td><?php echo MYSQL_DATABASE; ?></td></tr>
		<tr><td>MYSQL_USER</td><td><?php echo MYSQL_USER; ?></td></tr>
		<tr><td>SESSION_NAME</td><td><?php echo settings::get_session_name(); ?></td></tr>
		<tr><td>SESSION_TIMEOUT</td><td><?php echo settings::get_session_timeout(); ?></td></tr>
	</tbody>
	</table>




<?php

require_once 'includes/footer.inc.php';
?>
