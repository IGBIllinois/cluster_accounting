# Configuration

## settings.inc.php.dist
* Copy conf/settings.inc.php.dist to conf/settings.inc.php
```
cp conf/settings.inc.php.dist conf/settings.inc.php
```
* Edit conf/settings.inc.php for your setup.

## General Settings
* TITLE - Title for your site
* ADMIN_EMAIL - Email address for the from field when email bills get sent out
* ENABLE_LOG - Enable logging 
* LOG_FILE - Path to log file
* SESSION_NAME - Unique name for the session. Defaults to PHPSESSID
* SESSION_TIMEOUT - Timeout before you are forced logoff
* PASSWORD_RESET_URL - URL to website where you can reset your LDAP password if you have one

## Database Settings
* MYSQL_HOST - Mysql/MariaDB host
* MYSQL_USER - Mysql database user
* MYSQL_PASSWORD - Mysql passord
* MYSQL_DATABASE - Mysql database

## LDAP Settings
* LDAP_HOST - LDAP hostname, 2 hosts can be specified if you leave a space between them
* LDAP_BASE_DN - BaseDN of your ldap server
* LDAP_BIND_USER - Bind user (optional)
* LDAP_BIND_PASS - Bind password (optional)
* LDAP_SSL - Enable SSL (ldaps)
* LDAP_TLS - Enable TLS
* LDAP_PORT - LDAP Port (389 or 636)

## Scheduler Settings
* JOB_SCHEDULER - SLURM or TORQUE
* TORQUE_ACCOUNTING - If you are using Torque, this is the accounting folder torque logs are stored in
* TORQUE_JOB_LOGS - If you are using Torque, this is the folder job logs are stored in if you have this enabled

## Data/Job Settings
* ROOT_DATA_DIR - Root directories where folders to be billed are stored in.  Can specified multiple ones by seperating with a space (/home /home/labs)
* DATA_MIN_BILL - Minimal amount data will be billed at.  If less than this amount, it will not be billed
* RESERVE_PROCESSORS_FACTOR - Amount of variance between walltime and elapsed time before a warning will pop up saying to reserve correct number of processors in job details
* RESERVE_MEMORY_FACTOR - Amount of variance between used memory and reserved memory beforea warning will pop up saying to reserve correct amount of memory in job details


