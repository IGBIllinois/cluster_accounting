<?php
require_once 'includes/header.inc.php';

if (!$login_user->is_admin()) {
        exit;
}
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];
}
else {
        $start_date = date('Ym') . "01";
        $end_date = date('Ymd',strtotime('-1 second',strtotime('+1 month',strtotime($start_date))));
}

$month_name = date('F',strtotime($start_date));
$month = date('m',strtotime($start_date));
$year = date('Y',strtotime($start_date));
$url_navigation = html::get_url_navigation($_SERVER['PHP_SELF'],$start_date,$end_date);

$data_bill = data_functions::get_data_bill($db,$month,$year);
$data_html = "";
foreach ($data_bill as $value) {
	if ($value['Billed Cost'] > 0) {
		$data_html .= "<tr>";
		$data_html .= "<td>" . $value['Directory'] . "</td>";
		$data_html .= "<td>" . $value['Project'] . "</td>";
		$data_html .= "<td>" . $value['Terabytes'] . "</td>";
		$data_html .= "<td>$" . $value['Total Cost'] . "</td>";
		$data_html .= "<td>$" . $value['Billed Cost'] . "</td>";
		$data_html .= "<td>" . $value['CFOP'] . "</td>";
		$data_html .= "<td>" . $value['Activity Code'] . "</td>";
		$data_html .= "</tr>";
	}
}

?>
<h3>Data Billing Monthly Report - <?php echo $month_name . " " . $year; ?></h3>
<ul class='pager'>
        <li class='previous'><a href='<?php echo $url_navigation['back_url']; ?>'>Previous Month</a></li>
        
        <?php   
                $next_month = strtotime('+1 day', strtotime($end_date));
                $today = mktime(0,0,0,date('m'),date('d'),date('y'));
                if ($next_month > $today) {
                        echo "<li class='next disabled'><a href='#'>Next Month</a></li>";
                }
                else {
                        echo "<li class='next'><a href='" . $url_navigation['forward_url'] . "'>Next Month</a></li>";
                }
        ?>
</ul>

<table class='table table-striped table-condensed table-bordered'>
        <thead>
                <tr>
                        <th>Directory</th>
                        <th>Project</th>
			<th>Terabytes</th>
                        <th>Cost</th>
                        <th>Billed Amount</th>
                        <th>CFOP</th>
                        <th>Activity Code</th>
                </tr>
        </thead>
        <?php echo $data_html; ?>

        <tr>
                <td>Total Cost:</td>
                <td colspan='6'>$<?php echo data_stats::get_total_cost($db,$start_date,$end_date,1); ?>
                </td>
	</tr>
	<tr>
		<td>Billed Cost:</td>
		<td colspan='6'>$<?php echo data_stats::get_billed_cost($db,$start_date,$end_date,1); ?>
        </tr>

</table>

<form class='form-inline' action='report.php' method='post'>
        <input type='hidden' name='month' value='<?php echo $month; ?>'> <input
                type='hidden' name='year' value='<?php echo $year; ?>'> <select
                name='report_type' class='input-medium'>
                <option value='xls'>Excel 2003</option>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> <input class='btn btn-primary' type='submit'
                name='create_data_report' value='Download Full Report'>
	<input class='btn btn-primary' type='submit'
		name='create_data_boa_report' value='Download BOA Report'>
</form>

<?php

include_once 'includes/footer.inc.php';
?>
