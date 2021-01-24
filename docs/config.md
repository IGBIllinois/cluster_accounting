# Configuration

## settings.inc.php.dist
* Copy conf/settings.inc.php.dist to conf/settings.inc.php
```
cp conf/settings.inc.php.dist conf/settings.inc.php
```
* Edit conf/settings.inc.php for your setup.

## General Settings
* __TITLE__ - Title for your site
* __ADMIN_EMAIL__ - Email address for the from field when email bills get sent out
* __ENABLE_LOG__ - Enable logging 
* __LOG_FILE__ - Path to log file
* __SESSION_NAME__ - Unique name for the session. Defaults to cluster_accounting
* __SESSION_TIMEOUT__ - Timeout before you are forced logoff
* __PASSWORD_RESET_URL__ - URL to website where you can reset your LDAP password if you have one
* __BOA_CFOP__ - UofI Account number to bill against

## Database Settings
* __MYSQL_HOST__ - Mysql/MariaDB host
* __MYSQL_USER__ - Mysql database user
* __MYSQL_PASSWORD__ - Mysql passord
* __MYSQL_DATABASE__ - Mysql database

## LDAP Settings
* __LDAP_HOST__ - LDAP hostname, 2 hosts can be specified if you leave a space between them
* __LDAP_BASE_DN__ - BaseDN of your ldap server
* __LDAP_PEOPLE_OU__ - Full OU of location of your users
* __LDAP_GROUP_OU__ - Full OU of location of your groups
* __LDAP_BIND_USER__ - Bind user (optional)
* __LDAP_BIND_PASS__ - Bind password (optional)
* __LDAP_SSL__ - Enable SSL (ldaps)
* __LDAP_TLS__ - Enable TLS
* __LDAP_PORT__ - LDAP Port (389 or 636)

## Scheduler Settings
* __JOB_SCHEDULER__ - SLURM or TORQUE
* __TORQUE_ACCOUNTING__ - If you are using Torque, this is the accounting folder torque logs are stored in
* __TORQUE_JOB_LOGS__ - If you are using Torque, this is the folder job logs are stored in if you have this enabled

## Data/Job Settings
* __ROOT_DATA_DIR__ - Root directories where folders to be billed are stored in.  Can specified multiple ones by seperating with a space (/home /home/labs)
* __DATA_MIN_BILL__ - Minimal amount data will be billed at.  If less than this amount, it will not be billed
* __RESERVE_PROCESSORS_FACTOR__ - Amount of variance between walltime and elapsed time before a warning will pop up saying to reserve correct number of processors in job details
* __RESERVE_MEMORY_FACTOR__ - Amount of variance between used memory and reserved memory beforea warning will pop up saying to reserve correct amount of memory in job details


