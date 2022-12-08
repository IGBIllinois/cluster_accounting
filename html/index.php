<?php
require_once 'includes/main.inc.php';

$public_queues = functions::get_queues($db,'PUBLIC');
$public_queues_html = html::get_queue_rows($public_queues);

$private_queues = functions::get_queues($db,'PRIVATE');
$private_queues_html = html::get_queue_rows($private_queues);

$data_cost = data_functions::get_current_data_cost($db);
$data_html .= "<tr>";
$data_html .= "<td>$" . $data_cost->get_cost() . "</td>";
$data_html .= "</tr>";


require_once 'includes/header.inc.php';
?>
<div class='jumbotron col-sm-10 col-md-10 col-lg-10 col-xl-10'>
	<h1 class='display-4'>
		<img src="images/imark_bw.gif"
			style="padding: 0 10px 10px 0; vertical-align: text-top;">Biocluster
		Accounting
	</h1>
	<p>View, manage, and bill Biocluster usage and storage</p>
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
