<?php
require_once 'includes/graph_main.inc.php';

$top_count = 6;

$start_date = "";
$end_date = "";
$year = date('Y');
if (isset($_GET['year'])) {
	$year = $_GET['year'];
}

elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {

        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
	$start_date_obj = DateTime::createFromFormat("Ymd H:i:s",$start_date. " 00:00:00"); 
	$end_date_obj = DateTime::createFromFormat("Ymd H:i:s",$end_date. " 00:00:00");
}

$user_id = 0;
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
}

$graph_type = "";
if (isset($_GET['graph_type'])) {
	$graph_type = $_GET['graph_type'];
}


//Jobs Per Month
switch ($graph_type) {

	case 'jobs_per_month':
	        $stats = new statistics($db);
		$data = $stats->get_jobs_per_month($year);
		$xaxis = "month_name";
		$yaxis = "num_jobs";
		$title = "Jobs Per Month";
		\IGBIllinois\graphs::bar_graph($data,$xaxis,$yaxis,$title);
		break;

	//Billed Cost Per Month	
	case 'billed_cost_per_month':
	        $stats = new statistics($db);
		$title = "Billed Job Cost Per Month";
		$xaxis = "month_name";
		$yaxis = "billed_cost";
	        $data = $stats->get_job_billed_cost_per_month($year);
		\IGBIllinois\graphs::bar_graph($data,$xaxis,$yaxis,$title);
		break;

	//Total Cost Per Month
	case 'total_cost_per_month':
 	       $stats = new statistics($db);
        	$data = $stats->get_job_total_cost_per_month($year);
		$title = "Total Job Cost Per Month";
		$xaxis = "month_name";
		$yaxis = "total_cost";
		\IGBIllinois\graphs::bar_graph($data,$xaxis,$yaxis,$title);
		break;

	case 'top_job_users':
	        $stats = new statistics($db);
        	$jobs = $stats->get_top_job_users($start_date_obj,$end_date_obj,$top_count);
	        $data = array();
        	$i = 0;
 	       foreach ($jobs as $row)
        	{
                	$data[$i]['legend'] = $row['user_name'] . " - " . number_format($row['num_jobs']);
	                $data[$i]['value'] = $row['num_jobs'];
        	        $i++;
	        }
        	$title = "Top User Jobs";
	        \IGBIllinois\graphs::pie_graph($data,$title);
		break;

	case 'users_top_total_cost':
		$stats = new statistics($db);
        	$jobs = $stats->get_top_cost_users($start_date_obj,$end_date_obj,$top_count);
	        $data = array();
        	$i = 0;
	        foreach ($jobs as $row)
        	{
                	$data[$i]['legend'] = $row['user_name'] . " - $" . number_format($row['total_cost'],2);
	                $data[$i]['value'] = $row['total_cost'];
        	        $i++;
	        }
        	$title = "User's Top Job Total Cost";
	        \IGBIllinois\graphs::pie_graph($data,$title);
		break;


	case 'users_top_billed_cost':
 	       $stats = new statistics($db);
	        $jobs = $stats->get_top_billed_cost_users($start_date_obj,$end_date_obj,$top_count);
        	$data = array();
	        $i = 0;
        	foreach ($jobs as $row)
	        {
        	        $data[$i]['legend'] = $row['user_name'] . " - $" . number_format($row['billed_cost'],2);
                	$data[$i]['value'] = $row['billed_cost'];
	                $i++;
        	}
	        $title = "User's Top Job Billed Cost";
        	\IGBIllinois\graphs::pie_graph($data,$title);
		break;

	case 'user_total_cost_per_month':
 	       $stats = new statistics($db);
        	$title = "Total Job Cost Per Month";
	        $data = $stats->get_job_total_cost_per_month($year,$user_id);
		$xaxis = "month_name";
		$yaxis = "total_cost";
        	\IGBIllinois\graphs::bar_graph($data,$xaxis,$yaxis,$title);
		break;


	case 'user_billed_cost_per_month':
	        $stats = new statistics($db);
        	$title = "Billed Job Cost Per Month";
	        $data = $stats->get_job_billed_cost_per_month($year,$user_id);
		$xaxis = "month_name";
		$yaxis = "billed_cost";
        	\IGBIllinois\graphs::bar_graph($data,$xaxis,$yaxis,$title);
		break;

	case 'user_num_jobs_per_month':
	        $stats = new statistics($db);
        	$title = "Number of Jobs Per Month";
		$xaxis = "month_name";
		$yaxis = "num_jobs";
	        $data = $stats->get_jobs_per_month($year,$user_id);
        	\IGBIllinois\graphs::bar_graph($data,$xaxis,$yaxis,$title);
		break;


	//Graph of Total Data Usage per Month
	case 'data_usage_per_month':
		$data = data_stats::get_usage_per_month($db,$year);
		$xaxis = "month_name";
		$yaxis = "terabyte";
		$title = "Total Data Usage Per Month (Terabytes)";
		\IGBIllinois\graphs::bar_graph($data,$xaxis,$yaxis,$title);
		break;



	//Top 5 Data Users
	case 'top_data_usage':

		$title = "Top Data Usage";
		$result = data_stats::get_top_data_usage($db,$start_date_obj,$end_date_obj,$top_count);
		$data = array();
	        $i = 0;
        	foreach ($result as $row)
	        {
        	        $data[$i]['legend'] = $row['project'] . " - " . number_format($row['terabyte'],2) . "TB";
                	$data[$i]['value'] = $row['terabyte'];
	                $i++;
        	}
	        \IGBIllinois\graphs::pie_graph($data,$title);
		break;


	case 'data_usage_daily':
		break;

}


?>
