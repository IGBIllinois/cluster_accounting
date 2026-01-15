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
	return confirm("Are you sure you want to remove this directory?");
}

function confirm_edit_project_bill() {
	return confirm("Are you sure you want to update the previous bill for this project?");
}

function confirm_delete_project() {
	return confirm("Are you sure you want to delete this project?");
}

function confirm_disable_queue() {
	return confirm("Are you sure you want to delete this queue?");
}
function confirm_update_data_cost() {
	return confirm("Are you sure want to update the data storage cost?");
}
function enable_supervisors() {
	if (document.form.is_supervisor.checked == false) {
		document.form.supervisor_id.disabled = false;

	} else if (document.form.is_supervisor.checked == true) {
		document.form.supervisor_id.disabled = true;

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

function set_cfop_billtype_tab() {
	switch (cfop_billtype.value) {
                case 'cfop':
                        $('#billing_tab a[data-target="#nav-cfop"]').tab('show');
                        break;
                case 'custom':
                        $('#billing_tab a[data-target="#nav-custom"]').tab('show');
                        break;
                case 'no_bill':
                        $('#billing_tab a[data-target="#nav-nobill"]').tab('show');
                        break;
                default:
                        $('#billing_tab a[data-target="#nav-cfop"]').tab('show');
                        break;



        };
}

function set_cfop_billtype_value() {
	cfop_billtype.value = 'cfop';
	$('#billing_tab a[data-bs-toggle="tab"]').bind('click',function (e) {
                var tab = $(this).attr("data-bs-target");
                switch (tab) {
                        case '#nav-cfop':
                                cfop_billtype.value = 'cfop';
                                break;
                        case '#nav-custom':
                                cfop_billtype.value = 'custom';
                                break;
                        case '#nav-nobill':
                                cfop_billtype.value = 'no_bill';
                                break;
                        default:
                                cfop_billtype.value = 'cfop';
                                break;
                }
        });

}


$.fn.select2.defaults.set( "theme", "bootstrap-5" );
$.fn.select2.defaults.set( "width", "resolve" );

