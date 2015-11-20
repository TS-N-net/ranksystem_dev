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
execInBackground('php jobs/job_calc_user.php');
execInBackground('php jobs/job_update_groups.php');
execInBackground('php jobs/job_clean.php');
?>
