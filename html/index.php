<?php
require_once 'includes/main.inc.php';
$public_queues = functions::get_queues($db,'PUBLIC');
$public_queues_html = html::get_queue_rows($public_queues);

$private_queues = functions::get_queues($db,'PRIVATE');
$private_queues_html = html::get_queue_rows($private_queues);

$data_cost = data_functions::get_current_data_cost($db);
$data_html = "<tr>";
$data_html .= "<td>$" . $data_cost->get_cost() . "</td>";
$data_html .= "</tr>";

$start = 0;
$count = 10;
$running_jobs = job_functions::get_running_jobs($db,$login_user->get_user_id(),$start,$count);
$number_jobs = job_functions::get_num_running_jobs($db,$login_user->get_user_id());
$jobs_html = "";
if (count($running_jobs)) {
	foreach ($running_jobs as $job) {
		$state_html = "";
                switch($job['state']) {
                        case 'RUNNING':
                                $state_html = "<span class='badge badge-pill badge-success'>&nbsp;</span>";
                                break;
                        case 'PENDING':
                                $state_html = "<span class='badge badge-pill badge-info'>&nbsp;</span>";

                }

		$jobs_html .= "<tr>";
		$jobs_html .= "<td>" . $state_html . "</td>";
		$jobs_html .= "<td>" . $job['job_number'] . "</td>";
		$jobs_html .= "<td>" . $job['job_name'] . "</td>";
		$jobs_html .= "<td>" . $job['project'] . "</td>";
		$jobs_html .= "<td>" . $job['queue'] . "</td>";
		$jobs_html .= "<td>" . $job['elapsed_time'] . "</td>";
		$jobs_html .= "<td>" . $job['cpus'] . "</td>";
		$jobs_html .= "<td>" . $job['mem_reserved'] . "</td>";
		$jobs_html .= "<td>" . $job['gpus'] . "</td>";
		$jobs_html .= "<td>$" . $job['current_cost'] . "</td>";
		$jobs_html .= "</tr>";
	}
	if ($number_jobs > $count) {
		$jobs_html .= "<tr><td colspan='10'><a href='running_jobs.php'>Click Here</a> to view the rest of your jobs</td></tr>";
	}
}
else {
	$jobs_html = "<tr><td colspan='10'>No Running or Pending Jobs</td></tr>";
}
require_once 'includes/header.inc.php';
?>
<div class='row'>
<div class='col-sm-10 col-md-10 col-lg-10 col-xl-10'>
<div class='container text-sm-left p-5 bg-light'>
	<h1 class='display-4'>
		<img src="images/imark_bw.gif"
			style="padding: 0 10px 10px 0; vertical-align: text-top;">Biocluster
		Accounting
	</h1>
	<p>View, manage, and bill Biocluster usage and storage</p>
</div>
</div>
</div>
<br>
<div class='col-sm-10 col-md-10 col-lg-10 col-xl-10'>
<h3>Current and Pending Jobs</h3>
<p>List of jobs currenting running or pending with the estimated current cost of the job.  Updated every 10 minutes</p>
<div class='col-sm-4 col-md-5 col-lg-4 col-xl-4'>
	<ul class='list-inline'>
                <li class='list-inline-item'><span class='badge badge-pill badge-success'>&nbsp</span> Running Job</li>
                <li class='list-inline-item'><span class='badge badge-pill badge-info'>&nbsp</span> Pending Job</li>
        </ul>
</div>

<table class='table table-bordered table-sm table-striped'>
	<thead>
		<tr>
			<th>&nbsp;</th>
			<th>Job Number</th>
			<th>Job Name</th>
			<th>Project</th>			
			<th>Queue</th>
			<th>Elapsed Time</th>
			<th>Reserved CPUs</th>
			<th>Reserved Memory (GB)</th>
			<th>Reserved GPUs</th>
			<th>Current Cost</th>
			
		</tr>
	</thead>
	<tbody>
		<?php echo $jobs_html; ?>
	</tbody>

</table>

</div>

<div class='col-sm-10 col-md-10 col-lg-10 col-xl-10'>
<p><h3>Cluster Cost</h3>
<p>Cluster usage is calculated by taking the higher of the
	cpu usage or memory usage for the queue the job was submitted to.</p>
<strong>Public Queues</strong> - Queues everyone with access to the biocluster can submit to.
<p><table class='table table-bordered table-sm table-striped'>
	<thead>
		<tr>
			<th>Queue</th>
			<th>CPU cost per day</th>
			<th>Memory (GB) cost per day</th>
			<th>GPU cost per day</th>
		</tr>
	</thead>
	<?php echo $public_queues_html; ?>
</table>
</div>
<div class='col-sm-10 col-md-10 col-lg-10 col-xl-10'>
<p><strong>Private Queues</strong> - Queues specific to certain groups.  You must have authorization to submit to these queues.
<p><table class='table table-bordered table-sm table-striped'>
        <thead>
                <tr>
                        <th>Queue</th>
                        <th>CPU cost per day</th>
                        <th>Memory (GB) cost per day</th>
			<th>GPU cost per day</th>
                </tr>
        </thead>
        <?php echo $private_queues_html; ?>
</table>
</div>

<div class='col-sm-10 col-md-10 col-lg-10 col-xl-10'>
<p><h3>Data Storage Costs</h3>
<p><table class='table table-bordered table-sm table-striped'>
	<thead>
		<tr>
			<th>Cost (Terabytes per month)</th>
		</tr>
	</thead>
	<?php echo $data_html; ?>

</table>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
