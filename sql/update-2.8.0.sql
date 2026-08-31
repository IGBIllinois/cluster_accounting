CREATE TABLE long_running_job_notifications (
	notification_id INT NOT NULL AUTO_INCREMENT,
	notification_job_number VARCHAR(20) NOT NULL,
	notification_job_number_array INT NOT NULL DEFAULT 0,
	notification_job_user VARCHAR(25) NOT NULL,
	notification_job_start_time DATETIME NOT NULL,
	notification_count INT NOT NULL DEFAULT 0,
	notification_last_sent DATETIME,
	PRIMARY KEY(notification_id),
	CONSTRAINT long_running_job_notification UNIQUE(notification_job_number,notification_job_number_array,notification_job_user,notification_job_start_time)
);
