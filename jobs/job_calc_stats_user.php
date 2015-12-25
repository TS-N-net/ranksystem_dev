<?PHP
$starttime = microtime(true);
?>
<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem - Calc Stats (User)</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="../other/style.css.php" />
</head>
<body>
<?PHP
require_once(substr(dirname(__FILE__),0,-4).'other/config.php');
require_once(substr(dirname(__FILE__),0,-4).'lang.php');
require_once(substr(dirname(__FILE__),0,-4).'ts3_lib/TeamSpeak3.php');

$sqlerr = 0;

try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
	if (strlen($queryname)>27) $queryname = substr($queryname, 0, -3).'_su'; else $queryname = $queryname .'_su';
	if (strlen($queryname2)>26) $queryname2 = substr($queryname2, 0, -4).'_su2'; else $queryname2 = $queryname2.'_su2';
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
        }
    }

	if(($count_user = $mysqlcon->query("SELECT count(*) as count FROM ((SELECT u.uuid FROM $dbname.user AS u INNER JOIN $dbname.stats_user As s On u.uuid=s.uuid WHERE lastseen>1448319007) UNION (SELECT u.uuid FROM $dbname.user AS u LEFT JOIN $dbname.stats_user As s On u.uuid=s.uuid WHERE s.uuid IS NULL)) x")) === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	$count_user = $count_user->fetchAll(PDO::FETCH_ASSOC);
	$total_user = $count_user[0]['count'];

	if(($job_begin = $mysqlcon->query("SELECT timestamp FROM $dbname.job_check WHERE job_name='calc_user_limit'")) === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	$job_begin = $job_begin->fetchAll();
	$job_begin = $job_begin[0]['timestamp'];
	$job_end = ceil($total_user / 500) * 500;
	if ($job_begin >= $job_end) {
		$job_begin = 0;
		$job_end = 500;
	} else {
		$job_end = $job_begin + 500;
	}

	if(($uuids = $mysqlcon->query("(SELECT u.uuid,u.rank,u.cldbid FROM $dbname.user AS u INNER JOIN $dbname.stats_user As s On u.uuid=s.uuid WHERE lastseen>1448319007) UNION (SELECT u.uuid,u.rank,u.cldbid FROM $dbname.user AS u LEFT JOIN $dbname.stats_user As s On u.uuid=s.uuid WHERE s.uuid IS NULL) ORDER BY cldbid ASC LIMIT $job_begin, 500")) === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	$uuids = $uuids->fetchAll();
	foreach($uuids as $uuid) {
		$sqlhis[$uuid['uuid']] = array(
			"uuid" => $uuid['uuid'],
			"rank" => $uuid['rank'],
			"cldbid" => $uuid['cldbid']
		);
	}

	// Calc Client Stats
	
	if ($mysqlcon->exec("UPDATE $dbname.stats_user AS t LEFT JOIN $dbname.user AS u ON t.uuid=u.uuid SET t.removed='1' WHERE u.uuid IS NULL") === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	
	if(($statsuserhis = $mysqlcon->query("SELECT uuid, removed FROM $dbname.stats_user")) === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	$statsuserhis = $statsuserhis->fetchAll();
	foreach($statsuserhis as $userhis) {
		$uidarrstats[$userhis['uuid']] = $userhis['removed'];
	}
	unset($statsuserhis);

	if(isset($sqlhis)) {
		echo '<b>Update User Stats between ',$job_begin,' and ',$job_end,':</b><br>';
		if(($userdataweekbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)")) === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		$userdataweekbegin = $userdataweekbegin->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		if(($userdataweekend = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)")) === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		$userdataweekend = $userdataweekend->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		if(($userdatamonthbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)")) === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		$userdatamonthbegin = $userdatamonthbegin->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		if(($userdatamonthend = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)")) === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		$userdatamonthend = $userdatamonthend->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

		$allupdateuuid = '';
		$allupdaterank = '';
		$allupdatecountw = '';
		$allupdatecountm = '';
		$allupdateidlew = '';
		$allupdateidlem = '';
		$allupdatetotac = '';
		$allupdatebase64 = '';
		$allupdatecldtup = '';
		$allupdatecldtdo = '';
		$allupdateclddes = '';
		$allinsertuserstats = '';
		
		foreach ($sqlhis as $userstats) {
			try {
				$clientinfo = $ts3->clientInfoDb($userstats['cldbid']);

				if(isset($userdataweekend[$userstats['uuid']]) && isset($userdataweekbegin[$userstats['uuid']])) {
					$count_week = $userdataweekend[$userstats['uuid']][0]['count'] - $userdataweekbegin[$userstats['uuid']][0]['count'];
					$idle_week = $userdataweekend[$userstats['uuid']][0]['idle'] - $userdataweekbegin[$userstats['uuid']][0]['idle'];
				} else {
					$count_week = 0;
					$idle_week = 0;
				}
				if(isset($userdatamonthend[$userstats['uuid']]) && isset($userdatamonthbegin[$userstats['uuid']])) {
					$count_month = $userdatamonthend[$userstats['uuid']][0]['count'] - $userdatamonthbegin[$userstats['uuid']][0]['count'];
					$idle_month = $userdatamonthend[$userstats['uuid']][0]['idle'] - $userdatamonthbegin[$userstats['uuid']][0]['idle'];
				} else {
					$count_month = 0;
					$idle_month = 0;
				}

				if(isset($uidarrstats[$userstats['uuid']])) {
					$allupdateuuid = $allupdateuuid . "'" . $userstats['uuid'] . "',";
					$allupdaterank = $allupdaterank . "WHEN '" . $userstats['uuid'] . "' THEN '" . $userstats['rank'] . "' ";
					$allupdatecountw = $allupdatecountw . "WHEN '" . $userstats['uuid'] . "' THEN '" . $count_week . "' ";
					$allupdatecountm = $allupdatecountm . "WHEN '" . $userstats['uuid'] . "' THEN '" . $count_month . "' ";
					$allupdateidlew = $allupdateidlew . "WHEN '" . $userstats['uuid'] . "' THEN '" . $idle_week . "' ";
					$allupdateidlem = $allupdateidlem . "WHEN '" . $userstats['uuid'] . "' THEN '" . $idle_month . "' ";
					$allupdatetotac = $allupdatetotac . "WHEN '" . $clientinfo['client_totalconnections'] . "' THEN '" . $total_connections . "' ";
					$allupdatebase64 = $allupdatebase64 . "WHEN '" . $clientinfo['client_base64HashClientUID'] . "' THEN '" . $base64hash . "' ";
					$allupdatecldtup = $allupdatecldtup . "WHEN '" . $clientinfo['client_total_bytes_uploaded'] . "' THEN '" . $client_total_up . "' ";
					$allupdatecldtdo = $allupdatecldtdo . "WHEN '" . $clientinfo['client_total_bytes_downloaded'] . "' THEN '" . $client_total_down . "' ";
					$allupdateclddes = $allupdateclddes . "WHEN '" . $clientinfo['client_description'] . "' THEN '" . $client_description . "' ";
				} else {
					$allinsertuserstats = $allinsertuserstats . "('" . $userstats['uuid'] . "', '" .$userstats['rank'] . "', '" . $count_week . "', '" . $count_month . "', '" . $idle_week . "', '" . $idle_month . "', '" . $clientinfo['client_totalconnections'] . "', '" . $clientinfo['client_base64HashClientUID'] . "', '" . $clientinfo['client_total_bytes_uploaded'] . "', '" . $clientinfo['client_total_bytes_downloaded'] . "', '" . $clientinfo['client_description'] . "'),";
				}
				echo 'User ',$userstats['uuid'],' gets a new week count of ',$count_week,' month count of ',$count_month,' week idle of ',$idle_week,' month idle of ',$idle_month,'.<br>';
			} catch (Exception $e) {
				echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
				$sqlerr++;
			}
		}
		
		if ($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp=$job_end WHERE job_name='calc_user_limit'") === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		
		if ($allupdateuuid != '') {
			$allupdateuuid = substr($allupdateuuid, 0, -1);
			if ($mysqlcon->exec("UPDATE $dbname.stats_user set rank = CASE uuid $allupdaterank END, count_week = CASE uuid $allupdatecountw END, count_month = CASE uuid $allupdatecountm END, idle_week = CASE uuid $allupdateidlew END, idle_month = CASE uuid $allupdateidlem END, total_connections = CASE uuid $allupdateidlem END, base64hash = CASE uuid $allupdateidlem END, client_total_up = CASE uuid $allupdateidlem END, client_total_down = CASE uuid $allupdateidlem END, client_description = CASE uuid $allupdateidlem END WHERE uuid IN ($allupdateuuid)") === false) {
				echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
				$sqlerr++;
			}
		}

		if($allinsertuserstats != '') {
			$allinsertuserstats = substr($allinsertuserstats, 0, -1);
			if ($mysqlcon->exec("INSERT INTO $dbname.stats_user (uuid,rank,count_week,count_month,idle_week,idle_month,total_connections,base64hash,client_total_up,client_total_down,client_description) VALUES $allinsertuserstats") === false) {
				echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
				$sqlerr++;
			}
		}
	}
}
catch (Exception $e) {
    echo $lang['error'] . $e->getCode() . ': ' . $e->getMessage();
	$sqlerr++;
}

if ($sqlerr == 0) {
	//update job_check, set job as success
}

if ($showgen == 1) {
    $buildtime = microtime(true) - $starttime;
    echo '<br>', sprintf($lang['sitegen'], $buildtime, $total_user), '<br>';
}
?>
</body>
</html>