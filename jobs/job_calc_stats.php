<?PHP
$starttime = microtime(true);
$nowtime = time();
?>
<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem - Calc Stats</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="../other/style.css.php" />
<?PHP
require_once(substr(dirname(__FILE__),0,-4).'other/config.php');
require_once(substr(dirname(__FILE__),0,-4).'lang.php');
require_once(substr(dirname(__FILE__),0,-4).'ts3_lib/TeamSpeak3.php');

$sqlerr = 0;

try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
	if (strlen($queryname)>27) $queryname = substr($queryname, 0, -3).'_cs'; else $queryname = $queryname .'_cs';
	if (strlen($queryname2)>26) $queryname2 = substr($queryname2, 0, -4).'_cs2'; else $queryname2 = $queryname2.'_cs2';
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
	
	$total_user = 0;
	$total_online_time = 0;
	$total_active_time = 0;
	$total_inactive_time = 0;
	$country_string = '';
	$platform_string = '';
	if(($uuids = $mysqlcon->query("SELECT uuid,count,idle,platform,nation FROM $dbname.user")) === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	$uuids = $uuids->fetchAll();
	foreach($uuids as $uuid) {
		$sqlhis[$uuid['uuid']] = array(
			"uuid" => $uuid['uuid'],
			"count" => $uuid['count'],
			"idle" => $uuid['idle']
		);
		if ($uuid['nation']!=NULL) $country_string .= $uuid['nation'] . ' ';
		if ($uuid['platform']!=NULL) {
			$uuid_platform = str_replace(' ','',$uuid['platform']);
			$platform_string .= $uuid_platform . ' ';
		}
		$total_online_time = $total_online_time + $uuid['count'];
		$total_active_time = $total_active_time + $uuid['count'] - $uuid['idle'];
		$total_inactive_time = $total_inactive_time + $uuid['idle'];
	}
	
	// Event Handling each 6 hours
	// Duplicate users Table in snapshot Table
	if(($max_entry_usersnap = $mysqlcon->query("SELECT MAX(DISTINCT(timestamp)) AS timestamp FROM $dbname.user_snapshot")) === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	$max_entry_usersnap = $max_entry_usersnap->fetch(PDO::FETCH_ASSOC);
	$diff_max_usersnap = $nowtime - $max_entry_usersnap['timestamp'];
	if($diff_max_usersnap > 21600) {
		if(isset($sqlhis)) {
			$allinsertsnap = '';
			foreach ($sqlhis as $insertsnap) {
				$allinsertsnap = $allinsertsnap . "('$nowtime','" . $insertsnap['uuid'] . "', '" . $insertsnap['count'] . "', '" . $insertsnap['idle'] . "'),";
			}
			$allinsertsnap = substr($allinsertsnap, 0, -1);
			if ($allinsertsnap != '') {
				if($mysqlcon->exec("INSERT INTO $dbname.user_snapshot (timestamp, uuid, count, idle) VALUES $allinsertsnap") === false) {
					echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
					$sqlerr++;
				}
			}
		}
		//Delete old Entries in user_snapshot
		$deletiontime = $nowtime - 2678400;
		if($mysqlcon->exec("DELETE FROM $dbname.user_snapshot WHERE timestamp=$deletiontime") === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
	}

	// Calc Values for server stats
	if(($entry_snapshot_count = $mysqlcon->query("SELECT count(DISTINCT(timestamp)) AS timestamp FROM $dbname.user_snapshot")) === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	$entry_snapshot_count = $entry_snapshot_count->fetch(PDO::FETCH_ASSOC);
	if ($entry_snapshot_count['timestamp'] > 27) {
		// Calc total_online_week
		if(($snapshot_count_week = $mysqlcon->query("select (select sum(count) from $dbname.user_snapshot where timestamp=(select max(s2.timestamp) as value1 from (select distinct(timestamp) from $dbname.user_snapshot order by timestamp desc limit 28) as s2, $dbname.user_snapshot as s1 where s1.timestamp=s2.timestamp)) - (select sum(count) from $dbname.user_snapshot where timestamp=(select min(s2.timestamp) as value2 from (select distinct(timestamp) from $dbname.user_snapshot order by timestamp desc limit 28) as s2, $dbname.user_snapshot as s1 where s1.timestamp=s2.timestamp)) as count")) === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		$snapshot_count_week = $snapshot_count_week->fetch(PDO::FETCH_ASSOC);
		$total_online_week = $snapshot_count_week['count'];
	} else {
		$total_online_week = 0;
	}
	if ($entry_snapshot_count['timestamp'] > 119) {
		// Calc total_online_month
		if(($snapshot_count_month = $mysqlcon->query("select (select sum(count) from $dbname.user_snapshot where timestamp=(select max(s2.timestamp) as value1 from (select distinct(timestamp) from $dbname.user_snapshot order by timestamp desc limit 120) as s2, $dbname.user_snapshot as s1 where s1.timestamp=s2.timestamp)) - (select sum(count) from $dbname.user_snapshot where timestamp=(select min(s2.timestamp) as value2 from (select distinct(timestamp) from $dbname.user_snapshot order by timestamp desc limit 120) as s2, $dbname.user_snapshot as s1 where s1.timestamp=s2.timestamp)) as count")) === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		$snapshot_count_month = $snapshot_count_month->fetch(PDO::FETCH_ASSOC);
		$total_online_month = $snapshot_count_month['count'];
	} else {
		$total_online_month = 0;
	}

	$country_array = array_count_values(str_word_count($country_string, 1));
	arsort($country_array);
	$country_counter = 0;
	$country_nation_other = 0;
	foreach ($country_array as $k => $v) {
		$country_counter++;
		if ($country_counter == 1) {
			$country_nation_name_1 = $k;
			$country_nation_1 = $v;
		} elseif ($country_counter == 2) {
			$country_nation_name_2 = $k;
			$country_nation_2 = $v;
		} elseif ($country_counter == 3) {
			$country_nation_name_3 = $k;
			$country_nation_3 = $v;
		} elseif ($country_counter == 4) {
			$country_nation_name_4 = $k;
			$country_nation_4 = $v;
		} elseif ($country_counter == 5) {
			$country_nation_name_5 = $k;
			$country_nation_5 = $v;
		} else {
			$country_nation_other = $country_nation_other + $v;
		}
	}

	$platform_array = array_count_values(str_word_count($platform_string, 1));
	$platform_other = 0;
	$platform_1 = 0;
	$platform_2 = 0;
	$platform_3 = 0;
	$platform_4 = 0;
	$platform_5 = 0;
	foreach ($platform_array as $k => $v) {
		if ($k == "Windows") {
			$platform_1 = $v;
		} elseif ($k == "iOS") {
			$platform_2 = $v;
		} elseif ($k == "Linux") {
			$platform_3 = $v;
		} elseif ($k == "Android") {
			$platform_4 = $v;
		} elseif ($k == "OSX") {
			$platform_5 = $v;
		} else {
			echo '<br> Platform:'.$k.';'.$v;
			$platform_other = $platform_other + $v;
		}
	}
	if($ts3['virtualserver_status']=="online") {
		$server_status = 1;
	} elseif($ts3['virtualserver_status']=="offline") {
		$server_status = 2;
	} elseif($ts3['virtualserver_status']=="virtual online") {	
		$server_status = 3;
	} else {
		$server_status = 4;
	}

	$total_user = count($sqlhis);
	$server_used_slots = $ts3['virtualserver_clientsonline'] - $ts3['virtualserver_queryclientsonline'];
	$server_free_slots = $ts3['virtualserver_maxclients'] - $server_used_slots;
	$server_channel_amount = $ts3['virtualserver_channelsonline'];
	$server_ping = $ts3['virtualserver_total_ping'];
	$server_packet_loss = $ts3['virtualserver_total_packetloss_total'];
	$server_bytes_down = $ts3['virtualserver_total_bytes_downloaded'];
	$server_bytes_up = $ts3['virtualserver_total_bytes_uploaded'];
	$server_uptime = $ts3['virtualserver_uptime'];
	$server_id = $ts3['virtualserver_id'];
	$server_name = $ts3['virtualserver_name'];
	$server_pass = $ts3['virtualserver_flag_password'];
	$server_creation_date = $ts3['virtualserver_created'];
	$server_platform = $ts3['virtualserver_platform'];
	$server_weblist = $ts3['virtualserver_weblist_enabled'];
	$server_version = $ts3['virtualserver_version'];

	if($mysqlcon->exec("UPDATE $dbname.stats_server SET total_user='$total_user', total_online_time='$total_online_time', total_online_month='$total_online_month', total_online_week='$total_online_week', total_active_time='$total_active_time', total_inactive_time='$total_inactive_time', country_nation_name_1='$country_nation_name_1', country_nation_name_2='$country_nation_name_2', country_nation_name_3='$country_nation_name_3', country_nation_name_4='$country_nation_name_4', country_nation_name_5='$country_nation_name_5', country_nation_1='$country_nation_1', country_nation_2='$country_nation_2', country_nation_3='$country_nation_3', country_nation_4='$country_nation_4', country_nation_5='$country_nation_5', country_nation_other='$country_nation_other', platform_1='$platform_1', platform_2='$platform_2', platform_3='$platform_3', platform_4='$platform_4', platform_5='$platform_5', platform_other='$platform_other', server_status='$server_status', server_free_slots='$server_free_slots', server_used_slots='$server_used_slots', server_channel_amount='$server_channel_amount', server_ping='$server_ping', server_packet_loss='$server_packet_loss', server_bytes_down='$server_bytes_down', server_bytes_up='$server_bytes_up', server_uptime='$server_uptime', server_id='$server_id', server_name='$server_name', server_pass='$server_pass', server_creation_date='$server_creation_date', server_platform='$server_platform', server_weblist='$server_weblist', server_version='$server_version'") === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}

	// Stats for Server Usage
	if(($max_entry_serverusage = $mysqlcon->query("SELECT MAX(timestamp) AS timestamp FROM $dbname.server_usage")) === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
	$max_entry_serverusage = $max_entry_serverusage->fetch(PDO::FETCH_ASSOC);
	$diff_max_serverusage = $nowtime - $max_entry_serverusage['timestamp'];
	if ($max_entry_serverusage['timestamp'] == 0 || $diff_max_serverusage > 870) {
		if($mysqlcon->exec("INSERT INTO $dbname.server_usage (timestamp, clients) VALUES ($nowtime,$server_used_slots)") === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
	}
}
catch (Exception $e) {
    echo $lang['error'] . $e->getCode() . ': ' . $e->getMessage();
	$offline_status = array(110,257,258,1024,1026,1031,1032,1033,1034,1280,1793);
	if(in_array($e->getCode(), $offline_status)) {
		if($mysqlcon->exec("UPDATE $dbname.stats_server SET server_status='0'") === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
	}
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