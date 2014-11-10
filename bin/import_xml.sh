#!/bin/bash

for f in /var/spool/torque/job_logs/* ;
do
	echo "$f";
	torque_date=`basename $f`
	php xml.php $torque_date


done
