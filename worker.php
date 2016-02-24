<?php
$GLOBALS['exec'] = FALSE;
$GLOBALS['logfile'] = dirname(__FILE__).'/logs/log';
$GLOBALS['pidfile'] = dirname(__FILE__).'/logs/pid';

function checkProcess($pid = null) {
	if(!empty($pid)) {
		$check_pid = "ps ".$pid;
		$result = shell_exec($check_pid);
		if (count(preg_split("/\n/", $result)) > 2) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		if (file_exists($GLOBALS['pidfile'])) {
			$check_pid = "ps ".file_get_contents($GLOBALS['pidfile']);
			$result = shell_exec($check_pid);
			if (count(preg_split("/\n/", $result)) > 2) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
}

function start() {
	if (checkProcess() == FALSE) {
		echo "Starting the Ranksystem Bot.\n";
		$start = "php ".dirname(__FILE__)."/jobs/bot.php >> ".$GLOBALS['logfile']." 2>&1 & echo $! >> ".$GLOBALS['pidfile'];
		exec($start);
		if (checkProcess() == FALSE) {
			echo "Failed to start the Ranksystem Bot!\n";
		} else {
			echo "Successfully startet.\n";
		}
	} else {
		echo "The Ranksystem is already running.\n";
	}
	$GLOBALS['exec'] = TRUE;
}

function stop() {
	if (checkProcess() == TRUE) {
		echo "Stopping the Ranksystem Bot.\n";
		$pid = file_get_contents($GLOBALS['pidfile']);
		exec("rm -f ".$GLOBALS['pidfile']);
		echo "Wait for Bot is closed";
		$count_check=0;
		while (checkProcess($pid) == TRUE) {
			sleep(1);
			echo ".";
			$count_check++;
			if($count_check > 5) {
				break;
			}
		}
		if (checkProcess($pid) == TRUE) {
			echo "\nFailed to stop the Ranksystem Bot!\n";
		} else {
			echo "\nSuccessfully stopped.\n";
		}
	} else {
		echo "The Ranksystem seems not running.\n";
	}
	$GLOBALS['exec'] = TRUE;
}

function restart() {
	stop();
	start();
	$GLOBALS['exec'] = TRUE;
}

function check() {
	if (checkProcess() == FALSE) {
		if (file_exists($GLOBALS['pidfile'])) {
			exec("rm -f ".$GLOBALS['pidfile']);
		}
		start();
	}
	$GLOBALS['exec'] = TRUE;
}

function status() {
	if (checkProcess() == FALSE) {
		echo "The Ranksystem does not seem to run.\n";
	} else {
		echo "The Ranksystem seems to be running.\n";
	}
	$GLOBALS['exec'] = TRUE;
}

function help() {
	echo " Usage: php worker.php {start|stop|restart|check|status}\n\n",
		  "\t* start   \t\t [start Ranksystem Bot]\n",
		  "\t* stop    \t\t [stop Ranksystem Bot]\n",
		  "\t* restart \t\t [restart Ranksystem Bot]\n",
		  "\t* check   \t\t [check Ranksystem Bot is running; if not, start it; no output if all is ok]\n",
		  "\t* status  \t\t [output status Ranksystem Bot]\n";
	$GLOBALS['exec'] = TRUE;
}

if (isset($_SERVER['argv'][1]) == 0) {
	help();
} else {
	$cmd = $_SERVER['argv'][1];
	if ($cmd == 'start')	start();
	if ($cmd == 'stop')		stop();
	if ($cmd ==	'restart')	restart();
	if ($cmd ==	'check')	check();
	if ($cmd ==	'status')	status();
	if ($cmd == 'help')		help();

	if ($GLOBALS['exec'] == FALSE) echo " Error parameter '$cmd' not valid. Type \"php worker.php help\" to get a list of valid parameter.\n";
}
?>