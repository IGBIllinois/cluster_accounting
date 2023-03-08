<?php
require_once 'includes/graph_main.inc.php';

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {

	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
	$stats = new statistics($db);
	$jobs = $stats->get_top_job_users($start_date,$end_date,6);
	$data = array();
	$i = 0;
	foreach ($jobs as $row)
	{
		$data[$i]['legend'] = $row['user_name'] . " - " . $row['num_jobs'];
		$data[$i]['value'] = $row['num_jobs'];
		$i++;
	}
//	print_r($data);
	$title = "User Jobs";
	\IGBIllinois\graphs::pie_graph($data,$title);
}


?>
