# Cluster Accounting

[![Build Status](https://travis-ci.com/IGBIllinois/cluster_accounting.svg?branch=master)](https://travis-ci.com/IGBIllinois/cluster_accounting)

- Cluster Accounting records jobs submitted on an HPC cluster using Torque or SLURM.
- Records data usage of the shared filesystem for billing
- Supports different prices for CPU, Memory, GPUs in different queues.
- Emails users their monthly bill
- Download Job reports and billing reports
- Graphs show top users, jobs submitted, data usage
- Use on [University of Illinois Institute for Genomic Biology Biocluster](http://biocluster.igb.illinois.edu)

# Installation

## Prerequisites
- Apache Web Server [https://httpd.apache.org/](https://httpd.apache.org/)
- PHP >= 5.4
- PHP Mysql
- PHP LDAP
- PHP XML
- Composer [https://getcomposer.org/](https://getcomposer.org/)
- Mysql/MariaDB >= 5.5
- SLURM [https://slurm.schedmd.com/](https://slurm.schedmd.com/) or PBS Torque [https://adaptivecomputing.com/cherry-services/torque-resource-manager/](https://adaptivecomputing.com/cherry-services/torque-resource-manager/)
- LDAP Server for authentication

1.  Create an alias in apache configs that points to the html folder.  
```
Alias /accounting /var/www/accounting/html
```
2.  Create mysql database
```
CREATE DATABASE cluster_accounting CHARACTER SET utf8;
```
2.  Run sql/cluster_accounting.sql on the mysql server.
```

mysql -u root -p cluster_accounting < sql/cluster_accounting.sql
```
3.  Create a user/password on the mysql server which has select/insert/delete/update permissions on the cluster_accounting database.
```
CREATE USER 'cluster_accounting'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT,INSERT,DELETE,UPDATE ON cluster_accounting.* to 'cluster_accounting'@'localhost';
```
4.  Edit /conf/settings.inc.php to reflect your settings.  Detailed list of settings is at [docs/config.md](docs/config.md)
5.  Run composer to install php dependencies
```
composer install
```
6.  To enable cron to upload jobs and data, add conf/cron.conf to /etc/cron.d/
```
cp /var/www/accounting/conf/cron.dist /var/www/accounting/conf/cron
ln -s /var/www/accounting/conf/cron /etc/cron.d/cluster_accounting
```
7.  If you enabled logging, add logrotate script to /etc/logrotate.d
```
cp /var/www/accounting/conf/log_rotate.conf.dist /var/www/accounting/conf/log_rotate.conf
ln -s /var/www/accounting/conf/log_rotate.conf /etc/logrotate.d/accounting
```


