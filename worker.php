<?php
require_once(dirname(__FILE__).'/other/config.php');

// select jobs without or with bad status and msg to user on ts alternate email

function execInBackground($cmd, $jobname, $mysqlcon) {
	$timestamp = time();
	if($mysqlcon->exec("INSERT INTO $dbname.job_log (timestamp,job_name,status) VALUES ('$timestamp','$jobname','9')") === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
	} else {
		$jobid = $mysqlcon->lastInsertId();
		if (substr(php_uname(), 0, 7) == "Windows"){
			pclose(popen("start /B ".$cmd." ".$jobid, "r")); 
			echo 'run command '.$cmd.' '.$jobid.'<br>';
		}
		else {
			exec($cmd." ".$jobid." > /dev/null &");
			echo 'run command '.$cmd.' '.$jobid.'<br>';
		}
	}
}
execInBackground('php '.dirname(__FILE__).'/jobs/job_calc_user.php','calc_user',$mysqlcon);
execInBackground('php '.dirname(__FILE__).'/jobs/job_update_groups.php','update_groups',$mysqlcon);
execInBackground('php '.dirname(__FILE__).'/jobs/job_get_avatars.php','get_avtars',$mysqlcon);
execInBackground('php '.dirname(__FILE__).'/jobs/job_clean.php','clean',$mysqlcon);
execInBackground('php '.dirname(__FILE__).'/jobs/job_calc_stats.php','calc_stats',$mysqlcon);
execInBackground('php '.dirname(__FILE__).'/jobs/job_calc_stats_user.php','calc_stats_user',$mysqlcon);
?>