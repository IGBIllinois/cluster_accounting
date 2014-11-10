<?php 
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

include_once '../conf/settings.inc.php';

//Following code is to test if the script is being run from the command line or the apache server.
$sapi_type = php_sapi_name();
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.";
}
else {
	$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
	
	$directories = data_functions::get_all_directories($db);
	foreach ($directories as $directory) {
		$output = array();
		if ($directory['dir_exists']) {
			exec("./billstorage.pl " . $directory['data_dir_path'],$output);
			
			$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
			data_functions::add_data_usage($db,$directory['data_dir_id'],$output);
		}
	}
	
}

?>
