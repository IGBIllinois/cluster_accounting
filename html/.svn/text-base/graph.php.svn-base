<?php
require_once 'includes/graph_main.inc.php';

$start_date = "";
$end_date = "";
$year = date('Y');
if (isset($_GET['year'])) {
	$year = $_GET['year'];
}

elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {

        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
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
if ($graph_type == 'jobs_per_month') {
        $stats = new statistics($db);
	$data = $stats->get_jobs_per_month($year);
	$xaxis = "month_name";
	$yaxis = "num_jobs";
	$title = "Jobs Per Month";
	cluster_graph::bar_graph($data,$xaxis,$yaxis,$title);
}
//Billed Cost Per Month	
elseif ($graph_type == 'billed_cost_per_month') {
        $stats = new statistics($db);
	$title = "Billed Job Cost Per Month";
	$xaxis = "month_name";
	$yaxis = "billed_cost";
        $data = $stats->get_job_billed_cost_per_month($year);
	cluster_graph::bar_graph($data,$xaxis,$yaxis,$title);
}
//Total Cost Per Month
elseif ($graph_type == 'total_cost_per_month') {
        $stats = new statistics($db);
        $data = $stats->get_job_total_cost_per_month($year);
	$title = "Total Job Cost Per Month";
	$xaxis = "month_name";
	$yaxis = "total_cost";
	cluster_graph::bar_graph($data,$xaxis,$yaxis,$title);
}
elseif ($graph_type == "top_job_users") {
        $stats = new statistics($db);
        $jobs = $stats->get_top_job_users($start_date,$end_date,6);
        $data = array();
        $i = 0;
        foreach ($jobs as $row)
        {
                $data[$i]['legend'] = $row['user_name'] . " - " . number_format($row['num_jobs']);
                $data[$i]['value'] = $row['num_jobs'];
                $i++;
        }
        $title = "Top User Jobs";
        cluster_graph::pie_graph($data,$title);
}
elseif ($graph_type == "users_top_total_cost") {
	$stats = new statistics($db);
        $jobs = $stats->get_top_cost_users($start_date,$end_date,6);
        $data = array();
        $i = 0;
        foreach ($jobs as $row)
        {
                $data[$i]['legend'] = $row['user_name'] . " - $" . number_format($row['total_cost'],2);
                $data[$i]['value'] = $row['total_cost'];
                $i++;
        }
        $title = "User's Top Job Total Cost";
        cluster_graph::pie_graph($data,$title);


}
elseif ($graph_type == "users_top_billed_cost") {
        $stats = new statistics($db);
        $jobs = $stats->get_top_billed_cost_users($start_date,$end_date,6);
        $data = array();
        $i = 0;
        foreach ($jobs as $row)
        {
                $data[$i]['legend'] = $row['user_name'] . " - $" . number_format($row['billed_cost'],2);
                $data[$i]['value'] = $row['billed_cost'];
                $i++;
        }
        $title = "User's Top Job Billed Cost";
        cluster_graph::pie_graph($data,$title);
}

elseif ($graph_type == "user_total_cost_per_month") {
        $stats = new statistics($db);
        $title = "Total Job Cost Per Month";
        $data = $stats->get_job_total_cost_per_month($year,$user_id);
	$xaxis = "month_name";
	$yaxis = "total_cost";
        cluster_graph::bar_graph($data,$xaxis,$yaxis,$title);

}

elseif ($graph_type == "user_billed_cost_per_month") {
        $stats = new statistics($db);
        $title = "Billed Job Cost Per Month";
        $data = $stats->get_job_billed_cost_per_month($year,$user_id);
	$xaxis = "month_name";
	$yaxis = "billed_cost";
        cluster_graph::bar_graph($data,$xaxis,$yaxis,$title);

}
elseif ($graph_type == "user_num_jobs_per_month") {
        $stats = new statistics($db);
        $title = "Number of Jobs Per Month";
	$xaxis = "month_name";
	$yaxis = "num_jobs";
        $data = $stats->get_jobs_per_month($year,$user_id);
        cluster_graph::bar_graph($data,$xaxis,$yaxis,$title);

}

//Graph of Total Data Usage per Month
elseif ($graph_type == "data_usage_per_month") {
	$data = data_stats::get_usage_per_month($db,$year);
	$xaxis = "month_name";
	$yaxis = "terabyte";
	$title = "Total Data Usage Per Month (Terabytes)";
	cluster_graph::bar_graph($data,$xaxis,$yaxis,$title);
}

//Accumulated Graph with Data Usage Per Month with backup and no backup
elseif ($graph_type == "data_usage_per_month_accumulated") {
        $backup_data = data_stats::get_usage_per_month($db,$year,"backup");
	$no_backup_data =data_stats::get_usage_per_month($db,$year,"no_backup");
	$data[0] = $backup_data;
	$data[1] = $no_backup_data;
	$legend = array('Backup Data','No Backup Data');
        $xaxis = "month_name";
        $yaxis = "terabyte";
        $title = "Total Data Usage Per Month (Terabytes)";
        cluster_graph::accumulated_bar_plot($data,$xaxis,$yaxis,$title,$legend);
}

//Data Usage Per Month Backup Folder
elseif ($graph_type == "data_usage_per_month_backup") {
        $data = data_stats::get_usage_per_month($db,$year,"backup");
        $xaxis = "month_name";
        $yaxis = "terabyte";
        $title = "Backup Data Usage Per Month (Terabytes)";
        cluster_graph::bar_graph($data,$xaxis,$yaxis,$title);

}

//Data Usage Per Month No_Backup Folder
elseif ($graph_type == "data_usage_per_month_nobackup") {
        $data = data_stats::get_usage_per_month($db,$year,"no_backup");
        $xaxis = "month_name";
        $yaxis = "terabyte";
        $title = "No_Backup Data Usage Per Month (Terabytes)";
        cluster_graph::bar_graph($data,$xaxis,$yaxis,$title);

}

//Top 5 Data Users
elseif ($graph_type == 'top_data_usage') {

	$top = 6;
	$title = "Top Data Usage";
	$result = data_stats::get_top_data_usage($db,$start_date,$end_date,$top);
	$data = array();
        $i = 0;
        foreach ($result as $row)
        {
                $data[$i]['legend'] = $row['project'] . " - " . number_format($row['terabyte'],2) . "TB";
                $data[$i]['value'] = $row['terabyte'];
                $i++;
        }
        cluster_graph::pie_graph($data,$title);


}
?>
