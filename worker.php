<?php
function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows"){
        pclose(popen("start /B ". $cmd, "r")); 
    }
    else {
        exec($cmd . " > /dev/null &");
		echo 'run command '.$cmd.'<br>';
    }
}
execInBackground('php '.dirname(__FILE__).'/jobs/job_calc_user.php');
sleep(1);
execInBackground('php '.dirname(__FILE__).'/jobs/job_update_groups.php');
sleep(1);
execInBackground('php '.dirname(__FILE__).'/jobs/job_get_avatars.php');
sleep(1);
execInBackground('php '.dirname(__FILE__).'/jobs/job_clean.php');
sleep(1);
execInBackground('php '.dirname(__FILE__).'/jobs/job_calc_rank.php');
sleep(1);
execInBackground('php '.dirname(__FILE__).'/jobs/job_calc_stats.php');
sleep(1);
execInBackground('php '.dirname(__FILE__).'/jobs/job_calc_stats_user.php');
?>