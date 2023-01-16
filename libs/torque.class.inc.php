<?php

///////////////////////////
//
// torque.class.inc.php
//
// helper functions to help convert accounting string into needed format
//
///////////////////////////

class torque {

	private const MAX_JOB_SCRIPT_LENGTH = 65535;

	public static function add_accounting($db,$ldap,$job_data,$job_log_xml) {
		list(,$status,$job_number,$parameters_string) = explode(";",$job_data);

		if ($status == "E") {
			$job_number = strstr($job_number,'.',true);
			$qsub_script = self::get_qsub_script($job_number,$job_log_xml);
			//explodes string into array using a space as the seperator.
			$parameters = explode(" ",$parameters_string);
			//alters the parameters array so the key and value are set properly
			foreach($parameters as $key => $value) {

				if (strpos($value,"Resource_List.neednodes") !== false) {
					$result = self::get_nodes_ppn($value);
					$parameters['ppn'] = $result['ppn'];
					$parameters['nodes'] = $result['nodes'];
					unset($parameters[$key]);

				}
                        //All other values are seperated by a equals sign.  Just explode it and set the key/value pairs correct.
                        else {
                                list($parameter,$parameter_value) = explode("=",$value);
                                $parameters[$parameter] = $parameter_value;
                                unset($parameters[$key]);
                        }
                }

                //Set Accounting/Project String to username if user didn't specify one with qsub.
                if (!isset($parameters['account'])) {
                        $parameters['account'] = $parameters['user'];
                }

                //calculate the number of slots.  There are 2 ways to specify slots.  With -l nodes=x:ppn=y method
                //or with ncpus=x method.
                if (isset($parameters['Resource_List.ncpus']) && ($parameters['Resource_List.ncpus'] > $parameters['nodes'] * $parameters['ppn'])) {
                        $slots = $parameters['Resource_List.ncpus'];
                }
                elseif (isset($parameters['nodes']) && (isset($parameters['ppn']))) {
                        $slots = $parameters['nodes'] * $parameters['ppn'];
                }
                else {
                        $slots = 1;

                }

                if (!isset($parameters['Resource_List.mem'])) {
                        $parameters['Resource_List.mem'] = "0b";
                }


                $job = new job($db);
                //creates array that gets submitted to the job.class.inc.php with the required information
                $job_data = array('job_number'=>$job_number,
                                'job_user'=>$parameters['user'],
                                'job_project'=>$parameters['account'],
                                'job_queue_name'=>$parameters['queue'],
                                'job_name'=>$parameters['jobname'],
                                'job_slots'=>$slots,
                                'job_submission_time'=>date('Y-m-d H:i:s',$parameters['ctime']),
                                'job_start_time'=>date('Y-m-d H:i:s',$parameters['start']),
                                'job_end_time'=>date('Y-m-d H:i:s',$parameters['end']),
                                'job_ru_wallclock'=>self::convert_to_seconds($parameters['resources_used.walltime']),
                                'job_cpu_time'=>self::convert_to_seconds($parameters['resources_used.cput']),
                                'job_reserved_mem'=>self::convert_memory($parameters['Resource_List.mem']),
                                'job_used_mem'=>self::convert_memory($parameters['resources_used.mem']),
                                'job_exit_status'=>$parameters['Exit_status'],
				'job_exec_hosts'=>$parameters['exec_host'],
				'job_qsub_script'=>$qsub_script,
                                'job_maxvmem'=>self::convert_memory($parameters['resources_used.vmem'])
                );
                	return $job->create($job_data,$ldap);
        	}
	        return false;
	}
	//convert_to_seconds() - converts HH:MM:SS format into seconds
	//$time - string formatted as HH:MM:SS
	//returns integer
	public static function convert_to_seconds($time) {
		list($hours,$mins,$secs) = explode(':',$time);
		$seconds = $hours * 3600 + $mins * 60 + $secs;
		return $seconds;
	}

	//convert_memory() - converts string repesenting amount of memory into bytes.
	//$mem - string can be in the format of NUMBER + SUFFIX where suffix is either
	//b, kb, mb, gb, or tb.  example is 10gb or 45mb.
	//returns integer representing memory in bytes.
	public static function convert_memory($mem) {

		$result = preg_split('#(?<=\d)(?=[a-z])#i', strtolower($mem));
		$i = $result[1];
		$mem = $result[0];
		$bytes =0;
		switch ($i) {
			case "b":
				$bytes = $mem;
				break;
						
			case "kb":
				$bytes = $mem * 1024;
				break;
		
			case "mb":
				$bytes = $mem * 1048576;
				break;
		
			case "gb":
				$bytes = $mem * 1073741824;
				break;
			case "tb":
				$bytes = $mem * 1099511627776;
				break;
			default:
				$bytes =  0;
				break;
		}
		return $bytes;
	}

	//get_nodes_ppn()
	//splits the Resource_List.neednodes torque accounting string into
	//nodes and ppn or sets defaults to 1 node and 1 ppn if otherwise
	//$resource_string - part of torque accounting string with Resource_List.neednodes
	//returns array(nodes,ppn)
	public static function get_nodes_ppn($resource_string) {
		$parameters = array();
		//tests if the value contains Resource_list.nodes and ppn.  If it does, then do fancy string
		//manipulation to extract the nodes and processors per node information
		if ((strpos($resource_string,"Resource_List.neednodes") !== false) && (strpos($resource_string,"ppn") !== false)) {
		
			list($nodes_string,$ppn_string) = explode(":",$resource_string);
			list(,$parameters['nodes']) = explode("=",$nodes_string);
			list(,$parameters['ppn']) = explode("=",$ppn_string);
		}
		//tests if the value contains Resource_list.nodes and not the ppn.  If one submits job without ppn specified, this is what happens.
		//Then it manipulates to extract the nodes and sets ppn to 1.
		elseif ((strpos($resource_string,"Resource_List.neednodes") !== false) && (strpos($resource_string,"ppn") == false)) {
			list(,$nodes) = explode("=",$resource_string);
			//Tests if its a number, then set $parameters['nodes'] to the number.
			if (is_numeric($nodes)) {
				$parameters['nodes'] = $nodes;
			}
			//Else $nodes must contain letters which could be default,biotech,etc because this is what it defaults
			//to when a job is submitted without specifying -l nodes=x:ppn:y.
			else {
				$parameters['nodes'] = 1;
			}
			$parameters['ppn'] = 1;
		}
	
		return $parameters;
	}	

	public static function get_job_log_xml($date) {
		$xml_file = functions::get_torque_job_dir() . $date;
		if (file_exists($xml_file)) {
			$xml_string = file_get_contents($xml_file);
		
			$xml_string = self::fix_torque_xml_job_id($xml_string);
			$new_string = self::fix_job_script($xml_string);
			return simplexml_load_string($new_string);
			
		}
		return false;
	}

	public static function get_qsub_script($job_number,$job_xml) {
		if (strpos($job_number,"[")) {
			$job_number = substr($job_number,0,strpos($job_number,"["));
			$job_number .= "[]";
		}
		if ($job_xml) {

			foreach ($job_xml as $jobinfo) {
				$xml_job_number = strstr($jobinfo->JobId,".",true);
				if ($job_number == $xml_job_number) {
					$job_script = $jobinfo->job_script;
					$job_script = htmlspecialchars($job_script,ENT_QUOTES,'UTF-8');
					$job_script = addslashes($job_script);
					$job_script = trim(rtrim($job_script));
					if (strlen($job_script) > self::MAX_JOB_SCRIPT_LENGTH) {
						$job_script = substr($job_script,0,self::MAX_JOB_SCRIPT_LENGTH);
					}
					return $job_script;
			
				}
				
			}
		}
		return false;
	}


        private static function fix_job_script($xml_string) {
                $job_script_start = "<job_script>";
                $job_script_end = "</job_script>";
		$pointer = 0;
		do { 	
			$pointer = strpos($xml_string,$job_script_start,$pointer);
			
			if ($pointer) { 
	                        $pointer = $pointer + strlen($job_script_start);
        	               	$stop = strpos($xml_string,$job_script_end,$pointer);
				$length = $stop - $pointer - 1;
	        	        $substring = substr($xml_string,$pointer,$length);
				$formatted_substring = htmlspecialchars($substring,ENT_QUOTES,'UTF-8');
				$formatted_length = strlen($formatted_substring);
        	                $xml_string = substr_replace($xml_string,$formatted_substring,$pointer,$length);
				$pointer = $pointer + $formatted_length + strlen($job_script_end) + 1;
			}
			else {
				break;
			}
			
		} while ($pointer < strlen($xml_string));

		return $xml_string;

        }
	public static function add_torque_script($db,$job_data,$job_log_xml) {
                list(,$status,$job_number,$parameters_string) = explode(";",$job_data);

                if ($status == "E") {
                        $job_number = strstr($job_number,'.',true);
                        $qsub_script = self::get_qsub_script($job_number,$job_log_xml);
			$split_job_number = self::split_job_number($job_number);
			$job_exists_parameters[':job_number'] = $split_job_number['job_number'];
			if ($split_job_number['job_number_array'] == "") {
	                        $job_exists_sql = "SELECT job_id as job_id, job_qsub_script as script FROM jobs ";
	                        $job_exists_sql .= "WHERE job_number=:job_number LIMIT 1";
			}
			else {
				$job_exists_sql = "SELECT job_id as job_id, job_qsub_script as script FROM jobs ";
                                $job_exists_sql .= "WHERE job_number=:job_number ";
                                $job_exists_sql .= "AND job_number_array=:job_number_array ";
				$job_exists_parameters[':job_number_array'] = $split_job_number['job_number_array'];

			}			
			$result = $db->query($job_exists_sql,$job_exists_parameters);
			if (count($result) && (trim(rtrim($result[0]['script'])) == "") && ($qsub_script != "")) {
				$sql = "UPDATE jobs SET job_qsub_script=:qsub_script ";
                                $sql .= "WHERE job_id=:job_id LIMIT 1";
				$parameters = array(
					':job_id'=>$result[0]['job_id'],
					':qsub_script'=>$qsub_script
				);

				$final_result = $db->non_select_query($sql,$parameters);
				if ($final_result) {
					return array('RESULT'=>true,'MESSAGE'=>$job_number . " job script successfully added");
				}
				else {
					return array('RESULT'=>false,'MESSAGE'=>$job_number . " job script failed");
				}	

			}

                }
	}
	

	private static function split_job_number($job_number) {
                $job_number_array = 0;
                if (strpos($job_number,"[")) {
                        $hyphen_pos = strrpos($job_number,"[");
                        $job_number_array = substr($job_number, $hyphen_pos+1);
                        $job_number_array = substr($job_number_array,0,strlen($job_number_array)-1);
                        $job_number = substr($job_number,0,$hyphen_pos);
                        return array('job_number'=>$job_number,'job_number_array'=>$job_number_array);
                }
                else {
                        return array('job_number'=>$job_number,'job_number_array'=>"");
                }

        }


	public static function add_exec_host($db,$job_data) {
                list(,$status,$job_number,$parameters_string) = explode(";",$job_data);

                if ($status == "E") {
                        $job_number = strstr($job_number,'.',true);
                        //explodes string into array using a space as the seperator.
                        $parameters = explode(" ",$parameters_string);
                        //alters the parameters array so the key and value are set properly
                        foreach($parameters as $key => $value) {

                               	list($parameter,$parameter_value) = explode("=",$value);
				$parameters[$parameter] = $parameter_value;
				unset($parameters[$key]);
			}
			$exec_host = $parameters['exec_host'];
			$split_job_number = self::split_job_number($job_number);
                        if ($split_job_number['job_number_array'] == "") {
                                $job_exists_sql = "SELECT count(1) FROM jobs ";
                                $job_exists_sql .= "WHERE job_number=:job_number LIMIT 1";
				$job_exists_parameters = array(':job_number'=>$split_job_number['job_number']);
                                if (count($db->query($job_exists_sql,$job_exists_parameters))) {
                                        $sql = "UPDATE jobs SET job_exec_hosts=:exec_host ";
                                        $sql .= "WHERE job_number=:job_number LIMIT 1";
					$parameters = array(
						':job_number'=>$job_number,
						':exec_host'=>$exec_host
					);
                                        return $db->non_select_query($sql,$parameters);
                                }

                        }
                        else {
                                $job_exists_sql = "SELECT count(1) FROM jobs ";
                                $job_exists_sql .= "WHERE job_number=:job_number ";
                                $job_exists_sql .= "AND job_number_array=:job_number_array ";
				$job_exists_parameters = array(
					':job_number'=>$split_job_number['job_number'],
					':job_number_array'=>$split_job_number['job_number_array']
				);
                                if (count($db->query($job_exists_sql,$job_exists_parameters))) {
                                        $sql = "UPDATE jobs SET job_exec_hosts=:exec_host ";
                                        $sql .= "WHERE job_number=:job_number ";
                                        $sql .= "AND job_number_array=:job_number_array ";
                                        $sql .= "LIMIT 1";
					$parameters = array(
						':job_number'=>$split_job_number['job_number'],
						':job_number_array'=>$split_job_number['job_number_array'],
						':exec_host'=>$exec_host
					);
                                        return $db->non_select_query($sql,$parameters);
                                }
                        }



                }
		return false;
	}


	//fix_torque_xml_job_id()
	//$xml_string - torque job log from /var/spool/torque/job_logs/ converted to string
	//return - string - fixed job log xml string
	//There is a bug in torque that has Job_Id as a node name instead of JobId.  
	//This needs to be fixed before xml can be parsed
	private static function fix_torque_xml_job_id($xml_string) {

                        //It isn't properly formatted so we need to format it correctly. There is a bug in torque.
                        $xml_string = "<?xml version='1.0' encoding='utf-8'?><root>" . $xml_string . "</root>\n";
                        $search = array('Job_Id');
                        $replace = array('JobId');
                        $xml_string = str_replace($search,$replace,$xml_string);
			return $xml_string;



	}
}
?>
