<?php
require_once 'includes/main.inc.php';

if (!$login_user->is_admin()) {
        exit;
}

$selected_month = new DateTime(date('Y-m-01 00:00:00'));
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


require_once 'includes/header.inc.php';

?>

<h3>Reports</h3>
<br>
<h4>Users</h4>
<div class='row'>
<form class='form-inline' method='post' action='report.php'>
                <select class='form-control custom-select' name='report_type'>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select>&nbsp;
	<input class='btn btn-primary' type='submit'
                name='create_user_report' value='Download User List'>
</form>

</div>
<br>
<h4>Jobs</h4>
<div class='row'>
<form class='form-inline' action='report.php' method='post'>
	<?php echo $year_html; ?>
	<?php echo $month_html; ?>
        <select name='report_type' class='form-control custom-select'>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select>&nbsp;
        <input class='btn btn-primary' type='submit' name='create_job_report' value='Download Full Report'>&nbsp;
        <input class='btn btn-primary' type='submit' name='create_job_boa_report' value='Download BOA Report'>&nbsp;
        <input class='btn btn-primary' type='submit' name='create_job_fbs_report' value='Download FBS Report'>&nbsp;
        <input class='btn btn-primary' type='submit' name='create_job_custom_report' value='Download Custom Billing Report'>

</form>
</div>
<br>
<h4>Data Usage</h4>
<div class='row'>
<form class='form-inline' action='report.php' method='post'>
	<?php echo $year_html; ?>
        <?php echo $month_html; ?>
        <select class='custom-select' name='report_type'>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select>&nbsp;
        <input class='btn btn-primary' type='submit' name='create_data_report' value='Download Full Report'>&nbsp;
        <input class='btn btn-primary' type='submit' name='create_data_boa_report' value='Download BOA Report'>&nbsp;
        <input class='btn btn-primary' type='submit' name='create_data_fbs_report' value='Download FBS Report'>&nbsp;
        <input class='btn btn-primary' type='submit' name='create_data_custom_report' value='Download Custom Billing Report'>
</form>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
