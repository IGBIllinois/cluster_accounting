function confirm_disable_user() {
	return confirm("Are you sure you wish to delete this user?");
}
function confirm_enable_user() {
	return confirm("Are you sure you wish to enable this user?");
}
function confirm_edit_user() {
	return bootbox.confirm("Are you sure you wish to edit this user?");
}
function confirm_delete_queue() {
	return confirm("Are you sure you want to delete this queue?");
}
function confirm_delete_dir() {
	return confirm("Are you sure want to remove this directory from the accounting software?");
}
function enable_supervisors() {
	if (document.form.is_supervisor.checked == false) {
		document.form.supervisor_id.disabled = false;

	} else if (document.form.is_supervisor.checked == true) {
		document.form.supervisor_id.disabled = true;

	}
}

function enable_project_bill() {
	if (document.form.bill_project.checked == false) {
		document.form.cfop_1.disabled = false;
		document.form.cfop_2.disabled = false;
		document.form.cfop_3.disabled = false;
		document.form.cfop_4.disabled = false;
		document.form.activity.disabled = false;
		document.form.hide_cfop.disabled = false;
	} else if (document.form.bill_project.checked == true) {
		document.form.cfop_1.disabled = true;
		document.form.cfop_2.disabled = true;
		document.form.cfop_3.disabled = true;
		document.form.cfop_4.disabled = true;
		document.form.activity.disabled = true;
		document.form.hide_cfop.disabled = true;
	}

}


function cfop_advance_1() {
	var length = document.forms["form"].cfop_1.value.length;
	if (length == 1) {
		document.forms["form"].cfop_2.focus()
	}
}

function cfop_advance_2() {
	var length = document.forms["form"].cfop_2.value.length;
	if (length >= 6) {
		document.forms["form"].cfop_3.focus()
	}

}
function cfop_advance_3() {
	var length = document.forms["form"].cfop_3.value.length;
	if (length >= 6) {
		document.forms["form"].cfop_4.focus()
	}

}

function enable_new_cfop() {
	if (document.form.new_cfop.value == 0) {
		document.form.cfop_1.disabled = false;
                document.form.cfop_2.disabled = false;
                document.form.cfop_3.disabled = false;
                document.form.cfop_4.disabled = false;
                document.form.activity.disabled = false;
                document.form.hide_cfop.disabled = false;

	}
	else if (document.form.new_cfop.value != 0) {
                document.form.cfop_1.disabled = true;
                document.form.cfop_2.disabled = true;
                document.form.cfop_3.disabled = true;
                document.form.cfop_4.disabled = true;
                document.form.activity.disabled = true;
                document.form.hide_cfop.disabled = true;

	}
}

$.fn.select2.defaults.set( "theme", "bootstrap4" );
$.fn.select2.defaults.set( "width", "resolve" );

