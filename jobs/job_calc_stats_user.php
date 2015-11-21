<?PHP
$starttime = microtime(true);
?>
<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem - Calc Stats (User)</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="../other/style.css.php" />
<?PHP
require_once(substr(dirname(__FILE__),0,-4).'other/config.php');
require_once(substr(dirname(__FILE__),0,-4).'lang.php');
require_once(substr(dirname(__FILE__),0,-4).'ts3_lib/TeamSpeak3.php');


try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
    $nowtime           = time();
	if(strlen($queryname)>27) {
		$queryname = substr($queryname, 0, -3).'_su';
	} else {
		$queryname = $queryname .'_su';
	}
	if(strlen($queryname2)>26) {
		$queryname2 = substr($queryname2, 0, -4).'_su2';
	} else {
		$queryname2 = $queryname2.'_su2';
	}
    if ($slowmode == 1)
        sleep(1);
    try {
        $ts3->selfUpdate(array(
            'client_nickname' => $queryname
        ));
    }
    catch (Exception $e) {
        if ($slowmode == 1)
            sleep(1);
        try {
            $ts3->selfUpdate(array(
                'client_nickname' => $queryname2
            ));
            echo $lang['queryname'], '<br><br>';
        }
        catch (Exception $e) {
            echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
        }
    }
	
	$total_user = 0;
	$uuids = $mysqlcon->query("SELECT uuid,rank FROM $dbname.user");
	$uuids = $uuids->fetchAll();
	foreach($uuids as $uuid) {
		$sqlhis[$uuid['uuid']] = array(
			"uuid" => $uuid['uuid'],
			"rank" => $uuid['rank']
		);
	}
	$total_user = count($sqlhis);
	
	// Calc Client Stats
	$statsuserhis = $mysqlcon->query("SELECT uuid FROM $dbname.stats_user");
	$statsuserhis = $statsuserhis->fetchAll();
	foreach($statsuserhis as $userhis) {
		$uidarrstats[$userhis['uuid']] = 1;
	}
	unset($statsuserhis);

	if(isset($sqlhis)) {
		$userdataweekbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)");
		$userdataweekbegin = $userdataweekbegin->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		$userdataweekend = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)");
		$userdataweekend = $userdataweekend->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		$userdatamonthbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)");
		$userdatamonthbegin = $userdatamonthbegin->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
		$userdatamonthend = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)");
		$userdatamonthend = $userdatamonthend->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

		$allupdateuuid = '';
		$allupdaterank = '';
		$allupdatecountw = '';
		$allupdatecountm = '';
		$allupdateidlew = '';
		$allupdateidlem = '';
		$allinsertuserstats = '';
		
		foreach ($sqlhis as $userstats) {
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
			} else {
				$allinsertuserstats = $allinsertuserstats . "('" . $userstats['uuid'] . "', '" .$userstats['rank'] . "', '" . $count_week . "', '" . $count_month . "', '" . $idle_week . "', '" . $idle_month . "'),";
			}
		}

		if ($allupdateuuid != '') {
			$allupdateuuid = substr($allupdateuuid, 0, -1);
			if ($mysqlcon->exec("UPDATE $dbname.stats_user set rank = CASE uuid $allupdaterank END, count_week = CASE uuid $allupdatecountw END, count_month = CASE uuid $allupdatecountm END, idle_week = CASE uuid $allupdateidlew END, idle_month = CASE uuid $allupdateidlem END WHERE uuid IN ($allupdateuuid)") === false) {
				echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
			}
		}

		if($allinsertuserstats != '') {
			$allinsertuserstats = substr($allinsertuserstats, 0, -1);
			if ($mysqlcon->exec("INSERT INTO $dbname.stats_user (uuid, rank, count_week, count_month) VALUES $allinsertuserstats") === false) {
				echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
			}
		}
	}
}
catch (Exception $e) {
    echo $lang['error'] . $e->getCode() . ': ' . $e->getMessage();
}
if ($showgen == 1) {
    $buildtime = microtime(true) - $starttime;
    echo '<br>', sprintf($lang['sitegen'], $buildtime, $total_user), '<br>';
}
?>
</body>
</html>