#!/bin/bash

for f in /var/spool/torque/server_priv/accounting/* ;
do
	echo "$f\n";
	torque_date=`basename $f`
	php import_hosts.php $torque_date


done
