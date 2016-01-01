#!/usr/bin/php
<?PHP
set_time_limit(60);
$starttime = microtime(true);
$count_tsuser['count'] = 0;
$nowtime = time();

require_once(substr(dirname(__FILE__),0,-4).'other/config.php');
require_once(substr(dirname(__FILE__),0,-4).'lang.php');
require_once(substr(dirname(__FILE__),0,-4).'ts3_lib/TeamSpeak3.php');

$sqlerr = 0;

try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
	if (strlen($queryname)>27) $queryname = substr($queryname, 0, -3).'_cc'; else $queryname = $queryname .'_cc';
	if (strlen($queryname2)>26) $queryname2 = substr($queryname2, 0, -4).'_cc2'; else $queryname2 = $queryname2.'_cc2';
    if ($slowmode == 1) sleep(1);
    try {
        $ts3->selfUpdate(array('client_nickname' => $queryname));
    }
    catch (Exception $e) {
        if ($slowmode == 1) sleep(1);
        try {
            $ts3->selfUpdate(array('client_nickname' => $queryname2));
        }
        catch (Exception $e) {
            echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
			$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
			$sqlerr++;
        }
    }
	
	// clean old clients out of the database
	if ($cleanclients == 1 && $slowmode != 1) {
		$cleantime = $nowtime - $cleanperiod;
		if(($lastclean = $mysqlcon->query("SELECT * FROM $dbname.job_check WHERE job_name='check_clean'")) === false) {
			echo $lang['error'],print_r($mysqlcon->errorInfo());
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$lastclean = $lastclean->fetchAll();
		if(($dbuserdata = $mysqlcon->query("SELECT uuid FROM $dbname.user")) === false) {
			echo $lang['error'],print_r($mysqlcon->errorInfo());
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
		$countrs = $dbuserdata->rowCount();
		$uuids = $dbuserdata->fetchAll();
		if ($lastclean[0]['timestamp'] < $cleantime) {
			echo $lang['clean'],"\n";
			$start=0;
			$break=200;
			$clientdblist=array();
			$countdel=0;
			$countts=0;
			while($getclientdblist=$ts3->clientListDb($start, $break)) {
				$clientdblist=array_merge($clientdblist, $getclientdblist);
				$start=$start+$break;
				$count_tsuser=array_shift($getclientdblist);
				if ($start == 100000 || $count_tsuser['count'] <= $start) {
					break;
				}
				if ($slowmode == 1) sleep(1);
			}
			foreach($clientdblist as $uuidts) {
				$single_uuid = $uuidts['client_unique_identifier']->toString();
				$uidarrts[$single_uuid]= 1;
			}
			unset($clientdblist);
			
			foreach($uuids as $uuid) {
				if(isset($uidarrts[$uuid[0]])) {
					$countts++;
				} else {
					$deleteuuids[] = $uuid[0];
					$countdel++;
				}
			}

			unset($uidarrts);
			echo sprintf($lang['cleants'], $countts, $count_tsuser['count']),"\n";
			echo sprintf($lang['cleanrs'], $countrs),"\n";

			if(isset($deleteuuids)) {
				$alldeldata = '';
				foreach ($deleteuuids as $dellarr) {
					$alldeldata = $alldeldata . "'" . $dellarr . "',";
				}
				$alldeldata = substr($alldeldata, 0, -1);
				$alldeldata = "(".$alldeldata.")";
				if ($alldeldata != '') {
					if($mysqlcon->exec("DELETE FROM $dbname.user WHERE uuid IN $alldeldata") === false) {
						echo $lang['error'],print_r($mysqlcon->errorInfo());
						$sqlmsg .= print_r($mysqlcon->errorInfo());
						$sqlerr++;
					} else {
						echo sprintf($lang['cleandel'], $countdel),"\n";
						if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$nowtime' WHERE job_name='check_clean'") === false) {
							echo $lang['error'],print_r($mysqlcon->errorInfo());
							$sqlmsg .= print_r($mysqlcon->errorInfo());
							$sqlerr++;
						}
					}
				}
			} else {
				echo $lang['cleanno'],"\n";
				if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$nowtime' WHERE job_name='check_clean'") === false) {
					echo $lang['error'],print_r($mysqlcon->errorInfo());
					$sqlmsg .= print_r($mysqlcon->errorInfo());
					$sqlerr++;
				}
			}
		}
	}
}
catch (Exception $e) {
    echo $lang['error'] . $e->getCode() . ': ' . $e->getMessage();
	$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
	$sqlerr++;
}

if ($showgen == 1) {
    $buildtime = microtime(true) - $starttime;
    echo "\n",sprintf($lang['sitegen'], $buildtime, $count_tsuser['count']),"\n";
}

if ($sqlerr == 0) {
	if(isset($_SERVER['argv'][1])) {
		$jobid = $_SERVER['argv'][1];
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='0', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo $lang['error'],print_r($mysqlcon->errorInfo());
		}
	}
} else {
	if(isset($_SERVER['argv'][1])) {
		$jobid = $_SERVER['argv'][1];
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='1', err_msg='$sqlmsg', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo $lang['error'],print_r($mysqlcon->errorInfo());
		}
	}
}
?>