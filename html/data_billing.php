<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}
$selected_month = new DateTime(date('Y-m-01 00:00:00'));

if (isset($_GET['year']) && isset($_GET['month'])) {
        $year = $_GET['year'];
        $month = $_GET['month'];
        $selected_month = DateTime::createFromFormat("Y-m-d H:i:s",$year . "-" . $month . "-01 00:00:00");
}

$month_name = $selected_month->format('F');
$month = $selected_month->format('m');
$year = $selected_month->format('Y');

//////Year////////
$min_year = data_functions::get_minimal_year($db);
$year_html = "<select class='form-control' name='year'>";
for ($i=$min_year; $i<=date("Y");$i++) {
        if ($i == $year) { $year_html .= "<option value='" . $i . "' selected='true'>" . $i . "</option>"; }
        else { $year_html .= "<option value='" . $i . "'>" . $i . "</option>"; }
}
$year_html .= "</select>";

///////Month///////
$month_html = "<select class='form-control' name='month'>";
for ($i=1;$i<=12;$i++) {
        if ($i == $month) { $month_html .= "<option value='$i' selected='true'>" . $i . " - " . date('F', mktime(0, 0, 0, $i, 10)) . "</option>"; }
        else { $month_html .= "<option value='$i'>" . $i . " - " . date('F', mktime(0, 0, 0, $i, 10)) . "</option>"; }
}
$month_html .= "</select>";
$next_month = DateTime::createFromFormat('Y-m',$year . "-" . $month);
$next_month->modify('first day of next month');
$current_month = new DateTime();

$url_navigation = html::get_url_navigation_month($_SERVER['PHP_SELF'],$year,$month);


$data_bill = data_functions::get_data_bill($db,$month,$year);
$data_html = "";
if (count($data_bill)) {
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
}
else {
	$data_html = "<tr><td colspan='7'>No Data Billing</td></tr>"; 
}
require_once 'includes/header.inc.php';
?>
<h3>Data Billing Monthly Report - <?php echo $month_name . " " . $year; ?></h3>
<form class='form-inline' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='get'>
<div class='form-group'>
        <label for='month'>Month:</label>
        &nbsp;<?php echo $month_html; ?>
</div>&nbsp;
<div class='form-group'>
        <label for='year'>Year:</label>
        &nbsp; <?php echo $year_html; ?>
</div>
&nbsp;
<div class='form-group'>
        <button class='btn btn-primary' type='submit' name='selectedDate'>Get Records</button>
</div>
</form>

<p>
<div class='row'>
        <div class='col-sm-12 col-md-12 col-lg-12 col-xl-12'>
        <a class='btn btn-sm btn-primary' href='<?php echo $url_navigation['back_url']; ?>'>Previous Month</a>

        <?php
                if ($next_month > $current_month) {
                        echo "<div class='float-right'><a class='btn btn-sm btn-primary' onclick='return false;'>Next Month</a></div>";
                }
                else {
                        echo "<div class='float-right'><a class='btn btn-sm btn-primary' href='" . $url_navigation['forward_url'] . "'>Next Month</a></div>";
                }
        ?>
        </div>
</div>
<p>
<table class='table table-striped table-sm table-bordered'>
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
        <input type='hidden' name='month' value='<?php echo $month; ?>'>
	<input type='hidden' name='year' value='<?php echo $year; ?>'>
	<select class='custom-select' name='report_type'>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select>&nbsp;
	<input class='btn btn-primary' type='submit' name='create_data_report' value='Download Full Report'>&nbsp;
	<input class='btn btn-primary' type='submit' name='create_data_boa_report' value='Download BOA Report'>
</form>

<?php

require_once 'includes/footer.inc.php';
?>
