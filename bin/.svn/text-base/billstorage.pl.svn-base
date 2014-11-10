#!/usr/bin/perl

use File::Find;

$directory = $ARGV[0];


#initial counters set to zero for root space utilization
$rootbackup=0;
$rootnobackup=0;

opendir(DIRECTORY,$directory) or die "Cannot open main directory $directory\n";
my $backup=0;
my $no_backup=0;
my $backup_files=0;
my $no_backup_files=0;
#adds size of each file to size counter dependign on if no_backup is in the filepath
find sub { 
	if($File::Find::dir=~/no_backup/) { 
		$no_backup += -s; 
		$no_backup_files++;
	}else { 
		$backup+= -s; 
		$backup_files++;
	} 
}, "$directory";

closedir(DIRECTORY);
#print total of space used in directories owned by root, not saved, purely informational
print "backup:\t$backup\t$backup_files\n";
print "no_backup:\t$no_backup\t$no_backup_files\n";
exit 0;

