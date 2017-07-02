<?php
require_once 'includes/header.inc.php';

$public = 1;
$public_queues = functions::get_queues($db,$public);

$public_queues_html = html::get_queue_rows($public_queues);
$private = 0;
$private_queues = functions::get_queues($db,$private);
$private_queues_html = html::get_queue_rows($private_queues);

$data_costs = data_functions::get_data_costs($db);
$data_html = "";
foreach ($data_costs as $value) {
	$data_html .= "<tr>";
	$data_html .= "<td>" . $value['type'] . "</td>";
	$data_html .= "<td>$" . $value['cost'] . "</td>";
	$data_html .= "</tr>";

}
?>
<div class='hero-unit'>
	<h1>
		<img src="images/imark_bw.gif"
			style="padding: 0 10px 10px 0; vertical-align: text-top;">Biocluster
		Accounting
	</h1>
	<p>View, manage, and bill Biocluster usage and storage</p>
</div>
<div class='span8'>
<p><h3>Cluster Cost</h3>
<p>Cluster usage is calculated by taking the higher of the
	cpu usage or memory usage for the queue the job was submitted to.</p>
<strong>Public Queues</strong> - Queues everyone with access to the biocluster can submit to.
<p><table class='table table-bordered table-striped'>
	<thead>
		<tr>
			<th>Queue</th>
			<th>CPU cost per day</th>
			<th>Memory (GB) cost per day</th>
		</tr>
	</thead>
	<?php echo $public_queues_html; ?>
</table>
</div>
<div class='span8'>
<p><strong>Private Queues</strong> - Queues specific to certain groups.  You must have authorization to submit to these queues.
<p><table class='table table-bordered table-striped'>
        <thead>
                <tr>
                        <th>Queue</th>
                        <th>CPU cost per day</th>
                        <th>Memory (GB) cost per day</th>
                </tr>
        </thead>
        <?php echo $private_queues_html; ?>
</table>
</div>

<div class='span8'>
<p><h3>Data Storage Costs</h3>
<p><table class='table table-bordered table-striped'>
	<thead>
		<tr>
			<th>Type</th>
			<th>Cost (Terabytes per month)</th>
		</tr>
	</thead>
	<?php echo $data_html; ?>

</table>
</div>
<?php

include_once 'includes/footer.inc.php';
?>
