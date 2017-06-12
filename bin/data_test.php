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
			$data_dir = new data_dir($db,$directory['data_dir_id']);
			$size = $data_dir->get_dir_size();
			echo "Directory: " . $data_dir->get_directory() . " Size: " . $size . " Bytes\n";
			//$data_dir->add_usage($size);
			data_functions::calculate_cost($db,$directory['data_dir_id'],6,2017);

	}
	
}

?>
