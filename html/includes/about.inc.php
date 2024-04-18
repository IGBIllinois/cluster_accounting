<?php
if (!$login_user->is_admin()) {
	return;
}

?>
<div class='modal fade' id='aboutModal' tabindex='-1' role='dialog' aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class='modal-dialog modal-lg' role='document'>
        <div class='modal-content'>
        <div class='modal-header'>
                <h5 class='modal-title'>About</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                <span aria-hidden="true">&times;</span>
        </div>
<div class='modal-body'>

	<table class='table table-bordered table-sm'>
		<tbody>
		<tr><td>Code Website</td><td><a href='<?php echo settings::get_website_url(); ?>' target='_blank'><?php echo settings::get_website_url(); ?></a></td></tr>
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

	<table class='table table-bordered table-sm table-hover'>
	<thead>
		<tr><th>Setting</th><th>Value</th></tr>
	</thead>
	<tbody>
		<tr><td>ENABLE_LOG</td><td><?php echo settings::get_log_enabled() ? "true" : "false"; ?></td></tr>
		<tr><td>LOG_FILE</td><td><?php echo settings::get_logfile(); ?></td></tr>
		<tr><td>TIMEZONE</td><td><?php echo settings::get_timezone(); ?></td></tr>
		<tr><td>LDAP_HOST</td><td><?php echo settings::get_ldap_host(); ?></td></tr>
		<tr><td>LDAP_BASE_DN</td><td><?php echo settings::get_ldap_base_dn(); ?></td></tr>
		<tr><td>LDAP_SSL</td><td><?php echo settings::get_ldap_ssl() ? "true" : "false"; ?></td></tr>
		<tr><td>LDAP_TLS</td><td><?php echo settings::get_ldap_tls() ? "true" : "false"; ?></td></tr>
		<tr><td>LDAP_BIND_USER</td><td><?php echo settings::get_ldap_bind_user(); ?></td></tr>
		<tr><td>LDAP_PORT</td><td><?php echo settings::get_ldap_port(); ?></td></tr>
		<tr><td>MYSQL_HOST</td><td><?php echo settings::get_mysql_host(); ?></td></tr>
		<tr><td>MYSQL_DATABASE</td><td><?php echo settings::get_mysql_database(); ?></td></tr>
		<tr><td>MYSQL_USER</td><td><?php echo settings::get_mysql_user(); ?></td></tr>
		<tr><td>MYSQL_PORT</td><td><?php echo settings::get_mysql_port(); ?></td></tr>
		<tr><td>MYSQL_SSL</td><td><?php echo settings::get_mysql_ssl() ? "true" : "false"; ?></td></tr>
		<tr><td>SMTP_HOST</td><td><?php echo settings::get_smtp_host(); ?></td></tr>
		<tr><td>SMTP_PORT</td><td><?php echo settings::get_smtp_port(); ?></td></tr>
		<tr><td>SMTP_USERNAME</td><td><?php echo settings::get_smtp_username(); ?></td></tr>
		<tr><td>FROM</td><td><?php echo settings::get_from_email(); ?></td></tr>
		<tr><td>FROM_NAME</td><td><?php echo settings::get_from_name(); ?></td></tr>
		<tr><td>SESSION_NAME</td><td><?php echo settings::get_session_name(); ?></td></tr>
		<tr><td>SESSION_TIMEOUT</td><td><?php echo settings::get_session_timeout(); ?></td></tr>
		<tr><td>JOB_SCHEDULER</td><td><?php echo settings::get_job_scheduler(); ?></td></tr>
		<tr><td>REPORT_PREFIX</td><td><?php echo settings::get_report_prefix(); ?></td></tr>
	</tbody>
	</table>
</div>

</div>
</div>
</div>
