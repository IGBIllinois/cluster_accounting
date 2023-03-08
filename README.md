# Cluster Accounting
[![Build Status](https://github.com/IGBIllinois/cluster_accounting/actions/workflows/main.yml/badge.svg)](https://github.com/IGBIllinois/cluster_accounting/actions/workflows/main.yml)

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

* Git clone https://www.github.com/IGBIllinois/cluster_accounting or download tagged tar.gz
```
git clone https://github.com/IGBIllinois/cluster_accounting.git
```

1.  Add apache config to apache configuration to point to html directory
```
Alias /accounting /var/www/accounting/html
<Location /accounting>
	AllowOverride None
	Require all granted
</Location>
```
2.  Create mysql database
```
CREATE DATABASE cluster_accounting CHARACTER SET utf8;
```

3.  Run sql/cluster_accounting.sql on the mysql server.
```
mysql -u root -p cluster_accounting < sql/cluster_accounting.sql
```
4.  Create a user/password on the mysql server which has select/insert/delete/update permissions on the cluster_accounting database.
```
CREATE USER 'cluster_accounting'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT,INSERT,DELETE,UPDATE ON cluster_accounting.* to 'cluster_accounting'@'localhost';
```
5.  Copy conf/settings.inc.php.dist to conf/settings.inc.php.  Detailed list of settings is at [docs/config.md](docs/config.md)
```
cp conf/settings.inc.php.dist conf/settings.inc.php
```

6.  Run composer to install php dependencies
```
composer install
```
7.  To enable cron to upload jobs and data, add conf/cron.conf to /etc/cron.d/
```
cp /var/www/accounting/conf/cron.dist /var/www/accounting/conf/cron
ln -s /var/www/accounting/conf/cron /etc/cron.d/cluster_accounting
```
8.  If you enabled logging, add logrotate script to /etc/logrotate.d
```
cp /var/www/accounting/conf/log_rotate.conf.dist /var/www/accounting/conf/log_rotate.conf
ln -s /var/www/accounting/conf/log_rotate.conf /etc/logrotate.d/accounting
```


