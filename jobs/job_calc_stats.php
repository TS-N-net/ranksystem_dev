<?PHP
	// Event Handling each 6 hours
	
	// Duplicate users Table in snapshot Table
	$max_entry_usersnap = $mysqlcon->query("SELECT MAX(DISTINCT(timestamp)) AS timestamp FROM $dbname.user_snapshot");
	$max_entry_usersnap = $max_entry_usersnap->fetch(PDO::FETCH_ASSOC);
	$diff_max_usersnap = $nowtime - $max_entry_usersnap['timestamp'];
	if($diff_max_usersnap > 21600) {
		if ($sqlhis != '') {
			$allinsertsnap = '';
			foreach ($sqlhis as $insertsnap) {
				$allinsertsnap = $allinsertsnap . "('$nowtime','" . $insertsnap['uuid'] . "', '" . $insertsnap['count'] . "', '" . $insertsnap['idle'] . "'),";
			}
			$allinsertsnap = substr($allinsertsnap, 0, -1);
			if ($allinsertsnap != '') {
				if ($mysqlcon->exec("INSERT INTO $dbname.user_snapshot (timestamp, uuid, count, idle) VALUES $allinsertsnap") === false) {
					echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
				}
			}
		}	
	}
	
	// Calc Values for server stats
	$entry_snapshot_count = $mysqlcon->query("SELECT count(DISTINCT(timestamp)) AS timestamp FROM $dbname.user_snapshot");
	$entry_snapshot_count = $entry_snapshot_count->fetch(PDO::FETCH_ASSOC);
	if ($entry_snapshot_count['timestamp'] > 27) {
		// Calc total_online_week
		$snapshot_count_week = $mysqlcon->query("select (select sum(count) from $dbname.user_snapshot where timestamp=(select max(s2.timestamp) as value1 from (select distinct(timestamp) from $dbname.user_snapshot order by timestamp desc limit 28) as s2, $dbname.user_snapshot as s1 where s1.timestamp=s2.timestamp)) - (select sum(count) from $dbname.user_snapshot where timestamp=(select min(s2.timestamp) as value2 from (select distinct(timestamp) from $dbname.user_snapshot order by timestamp desc limit 28) as s2, $dbname.user_snapshot as s1 where s1.timestamp=s2.timestamp)) as count");
		$snapshot_count_week = $snapshot_count_week->fetch(PDO::FETCH_ASSOC);
		$total_online_week = $snapshot_count_week['count'];
	} else {
		$total_online_week = 0;
	}
	if ($entry_snapshot_count['timestamp'] > 119) {
		// Calc total_online_month
		$snapshot_count_month = $mysqlcon->query("select (select sum(count) from $dbname.user_snapshot where timestamp=(select max(s2.timestamp) as value1 from (select distinct(timestamp) from $dbname.user_snapshot order by timestamp desc limit 120) as s2, $dbname.user_snapshot as s1 where s1.timestamp=s2.timestamp)) - (select sum(count) from $dbname.user_snapshot where timestamp=(select min(s2.timestamp) as value2 from (select distinct(timestamp) from $dbname.user_snapshot order by timestamp desc limit 120) as s2, $dbname.user_snapshot as s1 where s1.timestamp=s2.timestamp)) as count");
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
	if($ts3_VirtualServer['virtualserver_status']=="online") {
		$server_status = 1;
	} elseif($ts3_VirtualServer['virtualserver_status']=="offline") {
		$server_status = 2;
	} elseif($ts3_VirtualServer['virtualserver_status']=="virtual online") {	
		$server_status = 3;
	} else {
		$server_status = 4;
	}
	
	$total_user = count($sqlhis);
	$server_used_slots = $ts3_VirtualServer['virtualserver_clientsonline'] - $ts3_VirtualServer['virtualserver_queryclientsonline'];
	$server_free_slots = $ts3_VirtualServer['virtualserver_maxclients'] - $server_used_slots;
	$server_channel_amount = $ts3_VirtualServer['virtualserver_channelsonline'];
	$server_ping = $ts3_VirtualServer['virtualserver_total_ping'];
	$server_packet_loss = $ts3_VirtualServer['virtualserver_total_packetloss_total'];
	$server_bytes_down = $ts3_VirtualServer['virtualserver_total_bytes_downloaded'];
	$server_bytes_up = $ts3_VirtualServer['virtualserver_total_bytes_uploaded'];
	$server_uptime = $ts3_VirtualServer['virtualserver_uptime'];
	$server_id = $ts3_VirtualServer['virtualserver_id'];
	$server_name = $ts3_VirtualServer['virtualserver_name'];
	$server_pass = $ts3_VirtualServer['virtualserver_flag_password'];
	$server_creation_date = $ts3_VirtualServer['virtualserver_created'];
	$server_platform = $ts3_VirtualServer['virtualserver_platform'];
	$server_weblist = $ts3_VirtualServer['virtualserver_weblist_enabled'];
	$server_version = $ts3_VirtualServer['virtualserver_version'];
	
	if ($mysqlcon->exec("UPDATE $dbname.stats_server SET total_user='$total_user', total_online_time='$total_online_time', total_online_month='$total_online_month', total_online_week='$total_online_week', total_active_time='$total_active_time', total_inactive_time='$total_inactive_time', country_nation_name_1='$country_nation_name_1', country_nation_name_2='$country_nation_name_2', country_nation_name_3='$country_nation_name_3', country_nation_name_4='$country_nation_name_4', country_nation_name_5='$country_nation_name_5', country_nation_1='$country_nation_1', country_nation_2='$country_nation_2', country_nation_3='$country_nation_3', country_nation_4='$country_nation_4', country_nation_5='$country_nation_5', country_nation_other='$country_nation_other', platform_1='$platform_1', platform_2='$platform_2', platform_3='$platform_3', platform_4='$platform_4', platform_5='$platform_5', platform_other='$platform_other', server_status='$server_status', server_free_slots='$server_free_slots', server_used_slots='$server_used_slots', server_channel_amount='$server_channel_amount', server_ping='$server_ping', server_packet_loss='$server_packet_loss', server_bytes_down='$server_bytes_down', server_bytes_up='$server_bytes_up', server_uptime='$server_uptime', server_id='$server_id', server_name='$server_name', server_pass='$server_pass', server_creation_date='$server_creation_date', server_platform='$server_platform', server_weblist='$server_weblist', server_version='$server_version'") === false) {
		echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
	}
	
	// Stats for Server Usage
	$max_entry_serverusage = $mysqlcon->query("SELECT MAX(timestamp) AS timestamp FROM $dbname.server_usage");
	$max_entry_serverusage = $max_entry_serverusage->fetch(PDO::FETCH_ASSOC);
	$diff_max_serverusage = $nowtime - $max_entry_serverusage['timestamp'];
	if ($max_entry_serverusage['timestamp'] == 0 || $diff_max_serverusage > 1800) {
		if ($mysqlcon->exec("INSERT INTO $dbname.server_usage (timestamp, clients) VALUES ($nowtime,$server_used_slots)") === false) {
			echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
		}
	}
	
	// Calc Client Stats
	$statsuserhis = $mysqlcon->query("SELECT uuid FROM $dbname.stats_user");
	$statsuserhis = $statsuserhis->fetchAll();
	/*
	            $sqlhis[$uuid['uuid']] = array(
				"uuid" => $uuid['uuid'],
                "cldbid" => $uuid['cldbid'],
                "count" => $uuid['count'],
                "lastseen" => $uuid['lastseen'],
                "grpid" => $uuid['grpid'],
                "nextup" => $uuid['nextup'],
                "idle" => $uuid['idle'],
                "cldgroup" => $uuid['cldgroup'],
                "boosttime" => $uuid['boosttime'],
				"rank" => $uuid['rank'],
				"platform" => $uuid['platform'],
				"nation" => $uuid['nation'],
				"version" => $uuid['version']
	*/
	echo 'before sql select snapshot: ' . memory_get_usage() . "<br>";
	if ($sqlhis != '') {
		$userdataweekbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)");
		$userdataweekbegin = $userdataweekbegin->fetchAll();
		$userdataweekend = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 28) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)");
		$userdatamonthbegin = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MIN(s2.timestamp) AS value2 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)");
		$userdatamonthend = $mysqlcon->query("SELECT uuid,count,idle FROM $dbname.user_snapshot WHERE timestamp=(SELECT MAX(s2.timestamp) AS value1 FROM (SELECT DISTINCT(timestamp) FROM $dbname.user_snapshot ORDER BY timestamp DESC LIMIT 120) AS s2, $dbname.user_snapshot AS s1 WHERE s1.timestamp=s2.timestamp)");
		$allinsertuserstats = '';
		foreach ($sqlhis as $userstats) {
			$count_week = '';
			$count_month = '';
			if(in_array($userstats['uuid'], $statsuserhis)) {
				//update
			} else {
				// else insert
				$allinsertuserstats = $allinsertuserstats . "('" . $userstats['uuid'] . "', '" .$userstats['rank'] . "', '" . $count_week . "', '" . $count_month . "'),";
				$allinsertuserstats = substr($allinsertuserstats, 0, -1);
				/*
				if ($allinsertuserstats != '') {
					if ($mysqlcon->exec("INSERT INTO $dbname.stats_user (uuid, rank, count_week, count_month) VALUES $allinsertuserstats") === false) {
						echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
					}
				}
				*/
			}
			
		}
		//echo $allinsertuserstats;
		/*
		$allinsertsnap = substr($allinsertsnap, 0, -1);
		if ($allinsertsnap != '') {
			if ($mysqlcon->exec("INSERT INTO $dbname.user_snapshot (timestamp, uuid, count, idle) VALUES $allinsertsnap") === false) {
				echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
			}
		}
		*/
	}
?>