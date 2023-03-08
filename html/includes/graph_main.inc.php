<?php

$include_paths = array(__DIR__ . '/../../libs');

set_include_path(get_include_path() . ":" . implode(':',$include_paths));
require_once __DIR__ . '/../../conf/app.inc.php';
require_once __DIR__ . '/../../conf/settings.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

function my_autoloader($class_name) {
        if(file_exists(__DIR__ . "/../../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');

date_default_timezone_set(settings::get_timezone());

$db = new \IGBIllinois\db(settings::get_mysql_host(),
                        settings::get_mysql_database(),
                        settings::get_mysql_user(),
                        settings::get_mysql_password(),
                        settings::get_mysql_ssl(),
                        settings::get_mysql_port()
                        );


?>
