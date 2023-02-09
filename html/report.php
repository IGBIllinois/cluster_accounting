<?php
require_once 'includes/main.inc.php';

$prefix = settings::get_report_prefix();

if (isset($_POST['create_job_report'])) {

	$month = $_POST['month'];
	$year = $_POST['year'];
	$type = $_POST['report_type'];
	$data = job_functions::get_jobs_bill($db,$month,$year);
	$filename = $prefix . "-jobs-" . $month . "-" . $year . "." . $type; 
}

elseif (isset($_POST['user_job_report'])) {
	$user = new user($db,$ldap,$_POST['user_id']);
	$type = $_POST['report_type'];
	$filename = $prefix . "-" . $user->get_username() . "-" . $_POST['start_date'] . "-" . $_POST['end_date'] . "." . $type;
	$data = $user->get_jobs_report($_POST['start_date'],$_POST['end_date']);
}
elseif (isset($_POST['job_report'])) {
	
        $type = $_POST['report_type'];
        $filename = $prefix . "-job-report-" . $_POST['start_date'] . "-" . $_POST['end_date'] . "." . $type;
        $data = job_functions::get_jobs($db,$_POST['user_id'],$_POST['search'],$_POST['completed'],$_POST['start_date'],$_POST['end_date']);
}

elseif (isset($_POST['project_report'])) {
	$type= $_POST['report_type'];
	$filename = $prefix . "-project-report." . $type;
	$data = functions::get_projects($db,1,$_POST['custom'],$_POST['search']);
}

elseif (isset($_POST['create_data_report'])) {
	$month = $_POST['month'];
        $year = $_POST['year'];
        $type = $_POST['report_type'];
        $data = data_functions::get_data_bill($db,$month,$year);
	$filename = $prefix . "-data-" . $month . "-" . $year . "." . $type;
}

elseif (isset($_POST['create_user_report'])) {
	$type = $_POST['report_type'];
	$data = user_functions::get_users($db,$ldap);
	$filename = $prefix . "-users." . $type;
}

elseif (isset($_POST['create_job_boa_report'])) {
	$month = $_POST['month'];
        $year = $_POST['year'];
        $type = $_POST['report_type'];
        $data = job_functions::get_jobs_boa_bill($db,$month,$year);
        $filename = $prefix . "-job-boa-" . $month . "-" . $year . "." . $type;



}
elseif (isset($_POST['create_data_boa_report'])) {

	$month = $_POST['month'];
        $year = $_POST['year'];
        $type = $_POST['report_type'];
        $data = data_functions::get_data_boa_bill($db,$month,$year);
        $filename = $prefix . "-data-boa-" . $month . "-" . $year . "." . $type;

}

elseif (isset($_POST['create_job_custom_report'])) {
	        $month = $_POST['month'];
        $year = $_POST['year'];
        $type = $_POST['report_type'];
        $data = job_functions::get_jobs_custom_bill($db,$month,$year);
        $filename = $prefix . "-job-custom-" . $month . "-" . $year . "." . $type;


}

elseif (isset($_POST['create_data_custom_report'])) {
                $month = $_POST['month'];
        $year = $_POST['year'];
        $type = $_POST['report_type'];
        $data = data_functions::get_data_custom_bill($db,$month,$year);
        $filename = $prefix . "-data-custom-" . $month . "-" . $year . "." . $type;


}

switch ($type) {
	case 'csv':
		\IGBIllinois\report::create_csv_report($data,$filename);
		break;
	case 'xlsx':
		\IGBIllinois\report::create_excel_2007_report($data,$filename);
		break;
}
?>
