# Configuration

## settings.inc.php.dist
* Copy conf/settings.inc.php.dist to conf/settings.inc.php
```
cp conf/settings.inc.php.dist conf/settings.inc.php
```
* Edit conf/settings.inc.php for your setup.

## General Settings
* \_\_TITLE\_\_ - Title for your site
* \_\_ADMIN_EMAIL\_\_ - Email address for the from field when email bills get sent out
* \_\_ENABLE_LOG\_\_ - Enable logging 
* \_\_LOG_FILE\_\_ - Path to log file
* \_\_SESSION_NAME\_\_ - Unique name for the session. Defaults to cluster_accounting
* \_\_SESSION_TIMEOUT\_\_ - Timeout before you are forced logoff
* \_\_PASSWORD_RESET_URL\_\_ - URL to website where you can reset your LDAP password if you have one
* \_\_BOA_CFOP\_\_ - UofI Account number to bill against

## Database Settings
* \_\_MYSQL_HOST\_\_ - Mysql/MariaDB host
* \_\_MYSQL_USER\_\_ - Mysql database user
* \_\_MYSQL_PASSWORD\_\_ - Mysql passord
* \_\_MYSQL_DATABASE\_\_ - Mysql database

## LDAP Settings
* \_\_LDAP_HOST\_\_ - LDAP hostname, 2 hosts can be specified if you leave a space between them
* \_\_LDAP_BASE_DN\_\_ - BaseDN of your ldap server
* \_\_LDAP_PEOPLE_OU\_\_ - Full OU of location of your users
* \_\_LDAP_GROUP_OU\_\_ - Full OU of location of your groups
* \_\_LDAP_BIND_USER\_\_ - Bind user (optional)
* \_\_LDAP_BIND_PASS\_\_ - Bind password (optional)
* \_\_LDAP_SSL\_\_ - Enable SSL (ldaps)
* \_\_LDAP_TLS\_\_ - Enable TLS
* \_\_LDAP_PORT\_\_ - LDAP Port (389 or 636)

## Scheduler Settings
* \_\_JOB_SCHEDULER\_\_ - SLURM or TORQUE
* \_\_TORQUE_ACCOUNTING\_\_ - If you are using Torque, this is the accounting folder torque logs are stored in
* \_\_TORQUE_JOB_LOGS\_\_ - If you are using Torque, this is the folder job logs are stored in if you have this enabled

## Data/Job Settings
* \_\_ROOT_DATA_DIR\_\_ - Root directories where folders to be billed are stored in.  Can specified multiple ones by seperating with a space (/home /home/labs)
* \_\_DATA_MIN_BILL\_\_ - Minimal amount data will be billed at.  If less than this amount, it will not be billed
* \_\_RESERVE_PROCESSORS_FACTOR\_\_ - Amount of variance between walltime and elapsed time before a warning will pop up saying to reserve correct number of processors in job details
* \_\_RESERVE_MEMORY_FACTOR\_\_ - Amount of variance between used memory and reserved memory beforea warning will pop up saying to reserve correct amount of memory in job details


