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

$sqlerr = 0;

if(($count_user = $mysqlcon->query("SELECT count(*) as count FROM $dbname.user")) === false) {
	echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
	$sqlerr++;
}
$count_user = $count_user->fetchAll(PDO::FETCH_ASSOC);
$total_user = $count_user[0]['count'];

if(($job_begin = $mysqlcon->query("SELECT timestamp FROM $dbname.job_check")) === false) {
	echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
	$sqlerr++;
}
$job_begin = $job_begin->fetchAll();
$job_begin = $job_begin[0]['timestamp'];
$job_end = ceil($total_user / 500) * 500;
if ($job_begin == $job_end) {
	$job_begin = 0;
	$job_end = 500;
} else {
	$job_end = $job_begin + 500;
}

if(($uuids = $mysqlcon->query("SELECT uuid,rank FROM $dbname.user ORDER BY cldbid ASC LIMIT $job_begin, 500")) === false) {
	echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
	$sqlerr++;
}
$uuids = $uuids->fetchAll();
foreach($uuids as $uuid) {
	$sqlhis[$uuid['uuid']] = array(
		"uuid" => $uuid['uuid'],
		"rank" => $uuid['rank']
	);
}

// Calc Client Stats
if(($statsuserhis = $mysqlcon->query("SELECT uuid FROM $dbname.stats_user")) === false) {
	echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
	$sqlerr++;
}
$statsuserhis = $statsuserhis->fetchAll();
foreach($statsuserhis as $userhis) {
	$uidarrstats[$userhis['uuid']] = 1;
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
		echo 'User ',$userstats['uuid'],' gets a new week count of ',$count_week,' month count of ',$count_month,' week idle of ',$idle_week,' month idle of ',$idle_month,'.<br>';
	}
	
	if ($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp=$job_end WHERE job_name='calc_user_limit'") === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	
	if ($allupdateuuid != '') {
		$allupdateuuid = substr($allupdateuuid, 0, -1);
		if ($mysqlcon->exec("UPDATE $dbname.stats_user set rank = CASE uuid $allupdaterank END, count_week = CASE uuid $allupdatecountw END, count_month = CASE uuid $allupdatecountm END, idle_week = CASE uuid $allupdateidlew END, idle_month = CASE uuid $allupdateidlem END WHERE uuid IN ($allupdateuuid)") === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
	}

	if($allinsertuserstats != '') {
		$allinsertuserstats = substr($allinsertuserstats, 0, -1);
		if ($mysqlcon->exec("INSERT INTO $dbname.stats_user (uuid,rank,count_week,count_month,idle_week,idle_month) VALUES $allinsertuserstats") === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
	}
}
if ($mysqlcon->exec("UPDATE $dbname.stats_user AS t LEFT JOIN $dbname.user AS u ON t.uuid=u.uuid SET t.removed='1' WHERE u.uuid IS NULL") === false) {
	echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
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