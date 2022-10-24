<?php

class html {

	//get_pages_html()
	//$url - url of page
	//$num_records - number of items
	//$start - start index of items
	//$count - number of items per page
	//returns pagenation to navigate between pages of devices
	public static function get_pages_html($url,$num_records,$start,$count) {

	        $num_pages = ceil($num_records/$count);
        	$current_page = $start / $count + 1;
	        if (strpos($url,"?")) {
        	        $url .= "&start=";
	        }
	        else {
        	        $url .= "?start=";
	        }

        	$pages_html = "<nav><ul class='pagination justify-content-center'>";

	        if ($current_page > 1) {
        	        $start_record = $start - $count;
                	$pages_html .= "<li class='page-item'><a class='page-link' href='" . $url . $start_record . "'>&laquo;</a></li> ";
	        }
        	else {
                	$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'>&laquo;</a></li>";
	        }

        	for ($i=0; $i<$num_pages; $i++) {
                	$start_record = $count * $i;
	                if ($i == $current_page - 1) {
        	                $pages_html .= "<li class='page-item disabled'>";
                	}
	                else {
        	                $pages_html .= "<li class='page-item'>";
                	}
	                $page_number = $i + 1;
        	        $pages_html .= "<a class='page-link' href='" . $url . $start_record . "'>" . $page_number . "</a></li>";
        	}

	        if ($current_page < $num_pages) {
        	        $start_record = $start + $count;
                	$pages_html .= "<li class='page-item'><a class='page-link' href='" . $url . $start_record . "'>&raquo;</a></li> ";
	        }
        	else {
                	$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'>&raquo;</a></li>";
	        }
        	$pages_html .= "</ul></nav>";
	        return $pages_html;

	}

	public static function get_num_pages($numRecords,$count) {
	        $numPages = floor($numRecords/$count);
        	$remainder = $numRecords % $count;
	        if ($remainder > 0) {
        	        $numPages++;
	        }
        	return $numPages;
	}

	public static function get_url_navigation($url,$start_date,$end_date,$get_array = array()) {
	        $previous_end_date = date('Ymd',strtotime('-1 second', strtotime($start_date)));
        	$previous_start_date = substr($previous_end_date,0,4) . substr($previous_end_date,4,2) . "01";
	        $next_start_date = date('Ymd',strtotime('+1 day', strtotime($end_date)));
        	$next_end_date = date('Ymd',strtotime('-1 second',strtotime('+1 month',strtotime($next_start_date))));
	        $next_get_array = array_merge(array('start_date'=>$next_start_date,'end_date'=>$next_end_date),$get_array);
        	$previous_get_array = array_merge(array('start_date'=>$previous_start_date,'end_date'=>$previous_end_date),$get_array);
	        $back_url = $url . "?" . http_build_query($previous_get_array);
        	$forward_url = $url . "?" . http_build_query($next_get_array);
	        return array('back_url'=>$back_url,'forward_url'=>$forward_url);

	}

	public static function get_jobs_rows($jobs,$start = 0,$count = 0) {
		$jobs_html = "";
		$max_job_name_length = 30;
		$i_start = 0;
		$i_count = count($jobs);
		if ($count) {
			$i_start = $start;
			$i_count = $start + $count;
		}
		if (count($jobs)) {
			for ($i=$i_start;$i<$i_count;$i++) {
				if (array_key_exists($i,$jobs)) {
		 	       		$jobs_html .= "<tr>";
					$job_name = $jobs[$i]['job_name'];
					if (strlen($job_name) >= $max_job_name_length) {
		                        	$job_name = $job_name . "...";
	        		        }

					if ($jobs[$i]['exit_status'] == "0:0") {
						$jobs_html .= "<td><span class='badge badge-pill badge-success'>&nbsp</span></td>";
					}
					elseif ($jobs[$i]['exit_status'] != "0:0") {
						$jobs_html .= "<td><span class='badge bage-pill badge-important'>&nbsp</span></td>";
					}
					$jobs_html .= "<td><a href='job.php?job=" . $jobs[$i]['job_number_full'] . "'>";
                                        $jobs_html .= $jobs[$i]['job_number_full'] . "</a></td>";
			        	$jobs_html .= "<td>" . $jobs[$i]['username'] . "</td>";
			        	$jobs_html .= "<td>" . $job_name . "</td>";
				        $jobs_html .= "<td>" . $jobs[$i]['project'] . "</td>";
				        $jobs_html .= "<td>" . $jobs[$i]['queue'] . "</td>";
				        $jobs_html .= "<td>" . $jobs[$i]['end_time'] . "</td>";
		        		if ($jobs[$i]['total_cost'] < 0.01)  {
			                	$jobs_html .= "<td><$0.01</td>";
				        }
				        else {
        		        		$jobs_html .= "<td>$" . $jobs[$i]['total_cost'] . "</td>";
				        }
				        if ($jobs[$i]['billed_cost'] < 0.01) {
				        	$jobs_html .= "<td><$0.01</td>";
				        }
				        else {
				        	$jobs_html .= "<td>$" . $jobs[$i]['billed_cost'] . "</td>";
				        }
			        	$jobs_html .= "</tr>";
				}
			}
		}
		else {
			$jobs_html .= "<tr><td colspan='9'>No Jobs</td></tr>";
		}
		return $jobs_html;
	}

	public static function get_users_rows($users,$start = 0,$count = 0) {
		$i_start = 0;
		$i_count = count($users);
		if ($count) {
			$i_start = $start;
			$i_count = $start + $count;
		}
		$users_html = "";
		for ($i=$i_start;$i<$i_count;$i++) {
		        if (array_key_exists($i,$users)) {
                		if ($users[$i]['user_admin']) {
		                        $user_admin = "<i class='fas fa-check'></i>";
                		}
	                	else {
        		                $user_admin = "<i class='fas fa-times'></i>";
		                }
                		$users_html .= "<tr>";
	                	$users_html .= "<td><a href='user.php?user_id=" . $users[$i]['user_id'] . "'>";
				$users_html .= $users[$i]['user_name'] . "</a></td>";
		                $users_html .= "<td>" . $users[$i]['user_full_name']. "</td>";
				if ($users[$i]['user_supervisor'] == '0') {
					$users_html .= "<td><i class='fas fa-check'></i></td>";
				}
				else {
					$users_html .= "<td><i class='fas fa-times'></i>";
				}
        		        $users_html .= "<td>" . $user_admin . "</td>";
				
				if ($users[$i]['user_ldap']) {
					$users_html .= "<td><i class='fas fa-check'></i></td>";
				}
				else {
					$users_html .= "<td><i class='fas fa-check'><i></td>";
				}
                		$users_html .= "</tr>";
			}
        	}
		return $users_html;
	}


	public static function get_queue_rows($queues) {
		$queues_html = "";
		foreach ($queues as $queue) {
			$queues_html .= "<tr>";
			$queues_html .= "<td>" . $queue['name'] . " - " . $queue['description'] . "</td>";
			$queues_html .= "<td>$" . number_format($queue['cost_cpu_day'],2) . "</td>";
			$queues_html .= "<td>$" . number_format($queue['cost_memory_day'],2) . "</td>";
			if ($queue['cost_gpu_day'] != 0.00) {
				$queues_html .= "<td>$" . number_format($queue['cost_gpu_day'],2) . "</td>";
			}
			else {
				$queues_html .= "<td>N/A</td>";
			}
			$queues_html .= "</tr>";	





		}

		return $queues_html;
	}

	public static function get_data_dir_rows($directories) {

		$dir_html = "";
		foreach ($directories as $directory) {
		        if ($directory['dir_exists']) {
                		$directory_exists = "<i class='fas fa-check'></i>";
	        	}
	        	else {
        	        	$directory_exists = "<i class='fas fa-times'></i>";
	        	}
	        	$dir_html .= "<tr>";
		        $dir_html .= "<td><a href='data_dir.php?data_dir_id=" . $directory['data_dir_id'] . "'>" . $directory['data_dir_path'] . "</a></td>";
        		$dir_html .= "<td>" . $directory_exists . "</td>";
		        $dir_html .= "<td><a href='edit_project.php?project_id=";
			$dir_html .= $directory['project_id'] ."'>" .  $directory['project_name'] . "</a></td>";
        		$dir_html .= "<td>" .  $directory['data_dir_time'] . "</td>";
		        $dir_html .= "</tr>";

		}
		return $dir_html;



	}

}

?>
