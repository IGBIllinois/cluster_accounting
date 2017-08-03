# Cluster Accounting

- Cluster Accounting records jobs submitted on an HPC cluster using Torque or SLURM.
- Records data usage of the shared filesystem for billing
- Supports different prices for CPU, Memory, GPUs in different queues.
- Emails users their monthly bill
- Download Job reports and billing reports
- Graphs show top users, jobs submitted, data usage
- Use on [University of Illinois Institute for Genomic Biology Biocluster](http://biocluster.igb.illinois.edu)

# Installation

## Prerequisites
- PHP
- PHP Mysql
- PHP LDAP
- PHP XML


1.  Create an alias in apache that points to html folder
2.  Run sql/cluster_accounting.sql on the mysql server.
```mysql -u root -p cluster_accounting < sql/cluster_accounting.sql```
3.  Create a user/password on the mysql server which has select/insert/delete/update permissions on the cluster_accounting database.
4.  Edit /conf/settings.inc.php to reflect your settings.
5.  Run composer to install php dependencies
```composer install```
6.  To /etc/crontab add
```
0 4 * * * root php /var/www/accounting/bin/data.php > /dev/null 2>&1
4 * * * * root php /var/www/accounting/bin/accounting.php --previous-hour > /dev/null 2>&1
0 4 1 * * root php /var/www/accounting/bin/calc_data_usage.php > /dev/null 2>&1
10 4 1 * * root php /var/www/accounting/bin/email.php > /dev/null 2>&1
```
7.  If you enabled logging, add logrotate script to /etc/logrotate.d
```ln -s /var/www/accounting/conf/log_rotate.conf /etc/logrotate.d/accounting```


