<?php

///////////////////////////
//
// slurm.class.inc.php
//
// helper functions to help convert accounting string into needed format
//
///////////////////////////

class slurm {

	private const SLURM_FORMAT = "State,JobID,User,JobName,Account,Partition,ExitCode,Submit,Start,End,Elapsed,ReqMem,MaxRSS,ReqCPUS,NodeList,MaxVMSize,TotalCPU,NTasks,NNodes,AllocTRES";
	private const SLURM_RUNNING_FORMAT = "State,JobID,User,JobName,Account,Partition,Submit,Start,Elapsed,ReqMem,ReqCPUS,NodeList,TotalCPU,NTasks,NNodes,AllocTRES";
	public const SLURM_STATES = "CA,CD,F,TO,OOM";
	public const SLURM_RUNNING_STATES = "PD,R";	
	private const SLURM_DELIMITER = "|";
	private const SLURM_TIME_FORMAT = "%Y-%m-%d %H:%M:%s";
	private const TRES_GPU = "gpu";

	public static function get_accounting($start_time,$end_time) {

		$exec = "export SLURM_TIME_FORMAT='" . self::SLURM_TIME_FORMAT . "'; ";
		$exec .= "sacct -anP --noconvert ";
		$exec .= "--format=" . self::SLURM_FORMAT . " ";
		$exec .= "--delimiter='" . self::SLURM_DELIMITER . "' ";
		$exec .= "--starttime='" . $start_time . "' ";
		$exec .= "--endtime='" . $end_time . "' ";
		$exec .= "--state=" . self::SLURM_STATES;
		$exit_status = 1;
		$output_array = array();
		exec($exec,$output_array,$exit_status);
		$job_data = array();
		foreach ($output_array as $job_array) {
			array_push($job_data,array_combine(explode(",",self::SLURM_FORMAT),explode(self::SLURM_DELIMITER,$job_array)));
		}
		return self::format_slurm_accounting($job_data);
	}

	public static function get_running_jobs() {
		$exec = "export SLURM_TIME_FORMAT='" . self::SLURM_TIME_FORMAT . "'; ";
                $exec .= "sacct -anP --noconvert ";
                $exec .= "--format=" . self::SLURM_RUNNING_FORMAT . " ";
                $exec .= "--delimiter='" . self::SLURM_DELIMITER . "' ";
                $exec .= "--state=" . self::SLURM_RUNNING_STATES;
                $exit_status = 1;
                $output_array = array();
                exec($exec,$output_array,$exit_status);
                $job_data = array();
                foreach ($output_array as $job_array) {
                        array_push($job_data,array_combine(explode(",",self::SLURM_RUNNING_FORMAT),explode(self::SLURM_DELIMITER,$job_array)));
                }
                return self::format_slurm_accounting($job_data);

	}
	public static function format_slurm_accounting($accounting) {

		foreach ($accounting as &$job) {
			$job_number = $job['JobID'];
			if (!strpos($job_number ,".batch")) {
				
				foreach ($accounting as $value) {
					if ($value['JobID'] == $job_number . ".batch") {
						if (isset($value['MaxRSS'])) {
							$job['MaxRSS'] = $value['MaxRSS'];
						}
						if (isset($value['MaxVMSize'])) {
							$job['MaxVMSize'] = $value['MaxVMSize'];
						}
						$job['NTasks'] = $value['NTasks'];
						
						break;	
					}
				}
			}

		}
		return $accounting;
	}


	public static function add_accounting($db,$ldap,$job_data) {

		$job_array_regex = "/^\d+_\[\d+-\d+\]/";
		$job_array_limit_regex = "/^\d+_\[\d+-\d+\%\d+\]/";
		if (!strpos($job_data['JobID'],".batch") && 
			!strpos($job_data['JobID'],".0") && 
			!strpos($job_data['JobID'],".extern") &&
			!preg_match($job_array_regex,$job_data['JobID']) &&
			!preg_match($job_array_limit_regex,$job_data['JobID'])
		) {
			$job = new job($db);
			if ($job_data['Account'] == "") {
				$job_data['Account'] = $job_data['User'];
			}

			//Memory per CPU
			if (strpos($job_data['ReqMem'],"c")) {
				$job_data['ReqMem'] = substr($job_data['ReqMem'],0,strpos($job_data['ReqMem'],"c"));
				$units = substr($job_data['ReqMem'],-1,1);
				$temp_mem = substr($job_data['ReqMem'],0,-1);
				$temp_mem = $temp_mem * $job_data['ReqCPUS'];
				$job_data['ReqMem'] = $temp_mem . $units;
			}
			//Memory Per Node
			elseif (strpos($job_data['ReqMem'],"n")) {
				$job_data['ReqMem'] = substr($job_data['ReqMem'],0,strpos($job_data['ReqMem'],"n"));
				$units = substr($job_data['ReqMem'],-1,1);
				$temp_mem = substr($job_data['ReqMem'],0,-1);
				$temp_mem = $temp_mem * $job_data['NNodes'];
				$job_data['ReqMem'] = $temp_mem . $units;
			}
			
			$gpu = 0;
			if (isset($job_data['AllocTRES']) && ($job_data['AllocTRES'] != "") && (str_contains($job_data['AllocTRES'],self::TRES_GPU))) {
				$search = "gres/" . self::TRES_GPU . "=";
				$start_position = strpos($job_data['AllocTRES'],$search) + strlen($search);
				$length = 1;
				$gpu = substr($job_data['AllocTRES'],$start_position,$length);
			}
		
                	//creates array that gets submitted to the job.class.inc.php with the required information
	                $job_insert = array('job_number'=>$job_data['JobID'],
        	                        'job_user'=>$job_data['User'],
                	                'job_project'=>$job_data['Account'],
                        	        'job_queue_name'=>$job_data['Partition'],
                                	'job_name'=>$job_data['JobName'],
	                                'job_slots'=>$job_data['ReqCPUS'],
        	                        'job_submission_time'=>$job_data['Submit'],
                	                'job_start_time'=>$job_data['Start'],
                        	        'job_end_time'=>$job_data['End'],
                                	'job_ru_wallclock'=>self::convert_to_seconds(self::format_slurm_time($job_data['Elapsed'])),
	                                'job_cpu_time'=>self::convert_to_seconds(self::format_slurm_time($job_data['TotalCPU'])),
        	                        'job_reserved_mem'=>self::convert_memory($job_data['ReqMem']),
					'job_used_mem'=>self::convert_memory($job_data['MaxRSS']),
                                        'job_exec_hosts'=>$job_data['NodeList'],
                                        'job_maxvmem'=>self::convert_memory($job_data['MaxVMSize']),
                        	        'job_exit_status'=>$job_data['ExitCode'],
					'job_qsub_script'=>'',
					'job_gpu'=>$gpu,
					'job_state'=>$job_data['State']
                	);
			return $job->create($job_insert,$ldap);
		}
		return array('RESULT'=> 0);
	        
	}

	//Add Running Job.  Addes running job to job_running table
	public static function add_running_job($db,$ldap,$job_data) {

                $job_array_regex = "/^\d+_\[\d+-\d+,%\]/";
                $job_array_limit_regex = "/^\d+_\[\d+-\d+\%\d+,%\]/";
                if (!strpos($job_data['JobID'],".batch") &&
                        !strpos($job_data['JobID'],".0") &&
                        !strpos($job_data['JobID'],".extern") &&
                        !preg_match($job_array_regex,$job_data['JobID']) &&
                        !preg_match($job_array_limit_regex,$job_data['JobID'])
                ) {
                        $running_job = new running_job($db);
                        if ($job_data['Account'] == "") {
                                $job_data['Account'] = $job_data['User'];
                        }

                        //Memory per CPU
                        if (strpos($job_data['ReqMem'],"c")) {
                                $job_data['ReqMem'] = substr($job_data['ReqMem'],0,strpos($job_data['ReqMem'],"c"));
                                $units = substr($job_data['ReqMem'],-1,1);
                                $temp_mem = substr($job_data['ReqMem'],0,-1);
                                $temp_mem = $temp_mem * $job_data['ReqCPUS'];
                                $job_data['ReqMem'] = $temp_mem . $units;
                        }
                        //Memory Per Node
                        elseif (strpos($job_data['ReqMem'],"n")) {
                                $job_data['ReqMem'] = substr($job_data['ReqMem'],0,strpos($job_data['ReqMem'],"n"));
                                $units = substr($job_data['ReqMem'],-1,1);
                                $temp_mem = substr($job_data['ReqMem'],0,-1);
                                $temp_mem = $temp_mem * $job_data['NNodes'];
                                $job_data['ReqMem'] = $temp_mem . $units;
                        }

                        $gpu = 0;
                        if (isset($job_data['AllocGRES']) && ($job_data['AllocGRES'] != "")) {
                                $gpu = substr($job_data['AllocGRES'],strpos($job_data['AllocGRES'],':') +1 );
                        }

			$start_time = "0000-00-00 00:00:00";
			if ($job_data['Start'] != 'Unknown') {
				$start_time = $job_data['Start'];
			}
                        //creates array that gets submitted to the job.class.inc.php with the required information
                        $job_insert = array('job_number'=>$job_data['JobID'],
                                        'job_user'=>$job_data['User'],
                                        'job_project'=>$job_data['Account'],
                                        'job_queue_name'=>$job_data['Partition'],
                                        'job_name'=>$job_data['JobName'],
                                        'job_slots'=>$job_data['ReqCPUS'],
                                        'job_submission_time'=>$job_data['Submit'],
                                        'job_start_time'=>$start_time,
                                        'job_ru_wallclock'=>self::convert_to_seconds(self::format_slurm_time($job_data['Elapsed'])),
                                        'job_cpu_time'=>self::convert_to_seconds(self::format_slurm_time($job_data['TotalCPU'])),
                                        'job_reserved_mem'=>self::convert_memory($job_data['ReqMem']),
                                        'job_exec_hosts'=>$job_data['NodeList'],
                                        'job_gpu'=>$gpu,
                                        'job_state'=>$job_data['State']
                        );
                        return $running_job->create($job_insert,$ldap);
                }
                return array('RESULT'=> 0);

        }


	//convert_to_seconds() - converts DD:HH:MM:SS
	//$time - string formatted as DD:HH:MM:SS
	//returns integer
	public static function convert_to_seconds($time) {
		$count = count(explode(":",$time));
		$days = 0;
		$hours = 0;
		$mins = 0;
		$secs = 0;
		switch($count) {

			case 2:
				list($mins,$secs) = explode(':',$time);
				break;
			case 3:
				list($hours,$mins,$secs) = explode(":",$time);
				break;
			case 4:
				list($days,$hours,$mins,$secs) = explode(':',$time);
				break;
		}
		$seconds = $days * 86400 + $hours * 3600 + $mins * 60 + $secs;
		return $seconds;
	}

	public static function format_slurm_time($slurm_time) {
		//Remove Microseconds
		if (strpos($slurm_time,".")) {
                                $slurm_time = substr($slurm_time,0,strpos($slurm_time,"."));
		}
		//Replace Day in format of DD- to DD:
		if (strpos($slurm_time,"-")) {
			$slurm_time = str_replace("-",":",$slurm_time);
		}

		return $slurm_time;
	}
	//convert_memory() - converts string repesenting amount of memory into bytes.
	//$mem - string can be in the format of NUMBER + SUFFIX where suffix is either
	//b, kb, mb, gb, or tb.  example is 10gb or 45mb.
	//returns integer representing memory in bytes.
	public static function convert_memory($mem) {
		$bytes = 0;
		if ($mem == "") {
			$bytes = 0;
		}
		else {
			$result = preg_split('#(?<=\d)(?=[a-z])#i', strtolower($mem));
			$unit = "";
			if (isset($result[1])) {
				$unit = $result[1];
			}
			$mem = $result[0];

			switch ($unit) {
				case "b":
					$bytes = $mem;
					break;
						
				case "kb":
					$bytes = $mem * 1024;
					break;
				case "k":
					$bytes = $mem * 1024;
					break;	
				case "mb":
					$bytes = $mem * 1048576;
					break;
	
				case "m":
					$bytes = $mem * 1048576;
					break;
				case "gb":
					$bytes = $mem * 1073741824;
					break;
				case "tb":
					$bytes = $mem * 1099511627776;
					break;
				default:
					$bytes =  $mem;
					break;
			}
		}
		return $bytes;
	}

}
?>
