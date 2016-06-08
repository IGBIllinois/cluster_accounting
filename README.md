#Cluster Acconting

PBS Torque Job Acounting Program
This reads the PBS Torque log files and inserts them into a database.  There is a web interface to view the jobs and to charge usage based on the queue the job was submitted to.

#Installation

*Prerequisites -
- PHP
- PHP Mysql
- PHP LDAP
- PHP XML


1.  Create an alias in apache that points to html folder
2.  Run sql/cluster_accounting.sql on the mysql server.
3.  Create a user/password on the mysql server which has select/insert/delete/update permissions on the cluster_accounting database.
4.  Edit /conf/settings.inc.php to reflect your settings.
5.  To /etc/crontab add
```
0 4 * * * root php scripts/accounting.php
0 4 L * * root php scripts/data.php
0 4 1 * * root php scripts/email.php
```
7.  All Done.

