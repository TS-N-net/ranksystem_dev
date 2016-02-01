<?php
//require_once(dirname(__FILE__).'/other/config.php');

// select jobs without or with bad status and msg to user on ts alternate email
//mail('admin@ts-n.net', 'Ranksystem Error Info', "test message");

$GLOBALS['name'] = "RankSystem";
$GLOBALS['exec'] = FALSE;

function checkProcess() {
	if (exec("screen -d | grep ".$GLOBALS['name'])) { return TRUE; } else { return FALSE; }
}

function start() {
	if (checkProcess() == FALSE) {
		echo "Starting the Ranksystem Bot.\n";
		exec("screen -AdmS ".$GLOBALS['name']." php ".dirname(__FILE__)."/jobs/bot.php > /dev/null &");
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
		exec("screen -S ".$GLOBALS['name']." -X quit > /dev/null &");
		if (checkProcess() == TRUE) {
			echo "Failed to stop the Ranksystem Bot!\n";
		} else {
			echo "Successfully stopped.\n";
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
		exec("screen -AdmS ".$GLOBALS['name']." php ".dirname(__FILE__)."/jobs/bot.php > /dev/null &");
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
		  "\t* check   \t\t [check Ranksystem Bot is running; if not, start it; without any output]\n",
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