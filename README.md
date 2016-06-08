cluster_accounting
==================

PBS Torque Job Acounting Program

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
This will grab accounting information everyday.  Calculate data usage on last day
of the month and email users their job information on the 1st of the month.
7.  All Done.

