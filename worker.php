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
execInBackground('php job_calc_user.php');
execInBackground('php job_update_groups.php');
execInBackground('php job_clean.php');
?>