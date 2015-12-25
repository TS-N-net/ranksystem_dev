<?PHP
$starttime = microtime(true);
$nowtime = time();
?>
<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem - Calc User</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="../other/style.css.php" />
<?PHP
echo '</head><body>';
require_once(substr(dirname(__FILE__),0,-4).'other/config.php');
if ($mysqlprob === false) {
	echo '<span class="wncolor">',$sqlconerr,'</span><br>';
	exit;
}
require_once(substr(dirname(__FILE__),0,-4).'lang.php');
require_once(substr(dirname(__FILE__),0,-4).'ts3_lib/TeamSpeak3.php');

$sqlerr = 0;

$debug = 'off';
if (isset($_GET['debug'])) {
	$checkdebug = file_get_contents('http://ts-n.net/ranksystem/token');
	if ($checkdebug == $_GET['debug'] && $checkdebug != '') {
		$debug = 'on';
	}
}

try {
	$ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
	if (strlen($queryname)>27) $queryname = substr($queryname, 0, -3).'_cu'; else $queryname = $queryname .'_cu';
	if (strlen($queryname2)>26) $queryname2 = substr($queryname2, 0, -4).'_cu2'; else $queryname2 = $queryname2.'_cu2';
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
			$sqlerr++;
		}
	}

	if ($update == 1) {
		$updatetime = $nowtime - $updateinfotime;
		if(($lastupdate = $mysqlcon->query("SELECT * FROM $dbname.job_check WHERE job_name='check_update'")) === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		$lastupdate = $lastupdate->fetchAll();
		if ($lastupdate[0]['timestamp'] < $updatetime) {
			set_error_handler(function() { });
			$newversion = file_get_contents('http://ts-n.net/ranksystem/version');
			restore_error_handler();
			if (substr($newversion, 0, 4) != substr($currvers, 0, 4) && $newversion != '') {
				echo '<b>', $lang['upinf'], '</b><br>';
				foreach ($uniqueid as $clientid) {
					if ($slowmode == 1) sleep(1);
					try {
						$ts3->clientGetByUid($clientid)->message(sprintf($lang['upmsg'], $currvers, $newversion));
						echo '<span class="sccolor">', sprintf($lang['upusrinf'], $clientid), '</span><br>';
					}
					catch (Exception $e) {
						echo '<span class="wncolor">', sprintf($lang['upusrerr'], $clientid), '</span><br>';
						$sqlerr++;
					}
				}
				echo '<br><br>';
			}
			if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp=$nowtime WHERE job_name='check_update'") === false) {
				echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
				$sqlerr++;
			}
		}
	}

	echo '<span class="hdcolor"><b>', $lang['crawl'], '</b></span><br>';
	if(($dbdata = $mysqlcon->query("SELECT * FROM $dbname.job_check WHERE job_name='calc_user_lastscan'")) === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		exit;
	}
	$lastscanarr = $dbdata->fetchAll();
	$lastscan = $lastscanarr[0]['timestamp'];
	if ($dbdata->rowCount() != 0) {
		if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$nowtime' WHERE job_name='calc_user_lastscan'") === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		if(($dbuserdata = $mysqlcon->query("SELECT uuid,cldbid,count,grpid,nextup,idle,boosttime FROM $dbname.user")) === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
		$uuids = $dbuserdata->fetchAll();
		foreach($uuids as $uuid) {
			$sqlhis[$uuid['uuid']] = array(
				"uuid" => $uuid['uuid'],
				"cldbid" => $uuid['cldbid'],
				"count" => $uuid['count'],
				"grpid" => $uuid['grpid'],
				"nextup" => $uuid['nextup'],
				"idle" => $uuid['idle'],
				"boosttime" => $uuid['boosttime']
			);
			$uidarr[] = $uuid['uuid'];
		}
	}
	unset($uuids);
	if ($debug == 'on') {
		echo '<br>sqlhis:<br><pre>', print_r($sqlhis), '</pre><br>';
	}
	if ($slowmode == 1) sleep(1);
	$allclients = $ts3->clientList();
	$yetonline[] = '';
	$insertdata  = '';
	if(empty($grouptime)) {
		echo '<span class="wncolor">',$lang['wiconferr'],'</span><br>';
		exit;
	}
	krsort($grouptime);
	$sumentries = 0;
	$serverquerycount = 0;
	$boosttime = 0;
	$nextupforinsert = key($grouptime) - 1;

	foreach ($allclients as $client) {
		$sumentries++;
		$cldbid   = $client['client_database_id'];
		$ip	   = ip2long($client['connection_client_ip']);
		$name	 = str_replace('\\', '\\\\', htmlspecialchars($client['client_nickname'], ENT_QUOTES));
		$uid	  = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
		$cldgroup = $client['client_servergroups'];
		$sgroups  = explode(",", $cldgroup);
		$platform=$client['client_platform'];
		$nation=$client['client_country'];
		$version=$client['client_version'];
		$firstconnect=$client['client_created'];
		if (!in_array($uid, $yetonline) && $client['client_version'] != "ServerQuery") {
			//$custominfo = $ts3->clientInfoDb($cldbid);
			$clientidle  = floor($client['client_idle_time'] / 1000);
			$yetonline[] = $uid;
			if (in_array($uid, $uidarr)) {
				$idle   = $sqlhis[$uid]['idle'] + $clientidle;
				$grpid  = $sqlhis[$uid]['grpid'];
				$nextup = $sqlhis[$uid]['nextup'];
				if ($sqlhis[$uid]['cldbid'] != $cldbid && $resetbydbchange == 1) {
					echo '<span class="wncolor">', sprintf($lang['changedbid'], $name, $uid, $cldbid, $sqlhis[$uid]['cldbid']), '</span><br>';
					$count = 1;
					$idle  = 0;
				} else {
					$hitboost = 0;
					if($boostarr!=0) {
						foreach($boostarr as $boost) {
							if(in_array($boost['group'], $sgroups)) {
								$boostfactor = $boost['factor'];
								$hitboost = 1;
								$boosttime = $sqlhis[$uid]['boosttime'];
								if($sqlhis[$uid]['boosttime']==0) {
									$boosttime = $nowtime;
								} else {
									if ($nowtime > $sqlhis[$uid]['boosttime'] + $boost['time']) {
										if ($slowmode == 1) sleep(1);
										try {
											$ts3->serverGroupClientDel($boost['group'], $cldbid);
											$boosttime = 0;
											echo '<span class="ifcolor">', sprintf($lang['sgrprm'], $sqlhis[$uid]['grpid'], $name, $uid, $cldbid), '</span><br>';
										}
										catch (Exception $e) {
											echo '<span class="wncolor">', sprintf($lang['sgrprerr'], $name, $uid, $cldbid), '</span><br>';
											$sqlerr++;
										}
									}
								}
								$count = ($nowtime - $lastscan) * $boost['factor'] + $sqlhis[$uid]['count'];
								if ($clientidle > ($nowtime - $lastscan)) {
									$idle = ($nowtime - $lastscan) * $boost['factor'] + $sqlhis[$uid]['idle'];
								}
							}
						}
					}
					if($boostarr == 0 or $hitboost == 0) {
						$count = $nowtime - $lastscan + $sqlhis[$uid]['count'];
						if ($clientidle > ($nowtime - $lastscan)) {
							$idle = $nowtime - $lastscan + $sqlhis[$uid]['idle'];
						}
					}
				}
				$dtF = new DateTime("@0");
				if ($substridle == 1) {
					$activetime = $count - $idle;
				} else {
					$activetime = $count;
				}
				$dtT = new DateTime("@$activetime");
				foreach ($grouptime as $time => $groupid) {
					if (in_array($groupid, $sgroups)) {
						$grpid = $groupid;
						break;
					}
				}
				$grpcount=0;
				foreach ($grouptime as $time => $groupid) {
					$grpcount++;
					if ($activetime > $time && !in_array($uid, $exceptuuid) && !array_intersect($sgroups, $exceptgroup)) {
						if ($sqlhis[$uid]['grpid'] != $groupid) {
							if ($sqlhis[$uid]['grpid'] != 0 && in_array($sqlhis[$uid]['grpid'], $sgroups)) {
								if ($slowmode == 1) sleep(1);
								try {
									$ts3->serverGroupClientDel($sqlhis[$uid]['grpid'], $cldbid);
									echo '<span class="ifcolor">', sprintf($lang['sgrprm'], $sqlhis[$uid]['grpid'], $name, $uid, $cldbid), '</span><br>';
								}
								catch (Exception $e) {
									echo '<span class="wncolor">', sprintf($lang['sgrprerr'], $name, $uid, $cldbid), '</span><br>';
									$sqlerr++;
								}
							}
							if (!in_array($groupid, $sgroups)) {
								if ($slowmode == 1) sleep(1);
								try {
									$ts3->serverGroupClientAdd($groupid, $cldbid);
									echo '<span class="ifcolor">', sprintf($lang['sgrpadd'], $groupid, $name, $uid, $cldbid), '</span><br>';
								}
								catch (Exception $e) {
									echo '<span class="wncolor">', sprintf($lang['sgrprerr'], $name, $uid, $cldbid), '</span><br>';
									$sqlerr++;
								}
							}
							$grpid = $groupid;
							if ($msgtouser == 1) {
								if ($slowmode == 1) sleep(1);
								$days  = $dtF->diff($dtT)->format('%a');
								$hours = $dtF->diff($dtT)->format('%h');
								$mins  = $dtF->diff($dtT)->format('%i');
								$secs  = $dtF->diff($dtT)->format('%s');
								if ($substridle == 1) {
									$ts3->clientGetByUid($uid)->message(sprintf($lang['usermsgactive'], $days, $hours, $mins, $secs));
								} else {
									$ts3->clientGetByUid($uid)->message(sprintf($lang['usermsgonline'], $days, $hours, $mins, $secs));
								}
							}
						}
						if($grpcount == 1) {
							$nextup = 0;
						}
						break;
					} else {
						$nextup = $time - $activetime;
					}
				}
				$updatedata[] = array(
					"uuid" => $uid,
					"cldbid" => $cldbid,
					"count" => $count,
					"ip" => $ip,
					"name" => $name,
					"lastseen" => $nowtime,
					"grpid" => $grpid,
					"nextup" => $nextup,
					"idle" => $idle,
					"cldgroup" => $cldgroup,
					"boosttime" => $boosttime,
					"platform" => $platform,
					"nation" => $nation,
					"version" => $version
				);
				if ($hitboost != 0) {
					echo sprintf($lang['upuserboost'], $name, $uid, $cldbid, $count, $activetime, $boostfactor), '<br>';
				} else {
					echo sprintf($lang['upuser'], $name, $uid, $cldbid, $count, $activetime), '<br>';
				}
			} else {
				$grpid = '0';
				foreach ($grouptime as $time => $groupid) {
					if (in_array($groupid, $sgroups)) {
						$grpid = $groupid;
						break;
					}
				}
				$insertdata[] = array(
					"uuid" => $uid,
					"cldbid" => $cldbid,
					"ip" => $ip,
					"name" => $name,
					"lastseen" => $nowtime,
					"grpid" => $grpid,
					"nextup" => $nextupforinsert,
					"cldgroup" => $cldgroup,
					"platform" => $platform,
					"nation" => $nation,
					"version" => $version,
					"firstcon" => $firstconnect
				);
				$uidarr[] = $uid;
				echo '<span class="sccolor">', sprintf($lang['adduser'], $name, $uid, $cldbid), '</span><br>';
			}
		} else {
			echo '<span class="wncolor">', sprintf($lang['nocount'], $name, $uid, $cldbid), '</span><br>';
			if($client['client_version'] == "ServerQuery") {
				$serverquerycount++;
			}
		}
	}

	if($mysqlcon->exec("UPDATE $dbname.user SET online=''") === false) {
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}

	if ($debug == 'on') {
		echo '<br>insertdata:<br><pre>', print_r($insertdata), '</pre><br>';
	}	

	if ($insertdata != '') {
		$allinsertdata = '';
		foreach ($insertdata as $insertarr) {
			$allinsertdata = $allinsertdata . "('" . $insertarr['uuid'] . "', '" . $insertarr['cldbid'] . "', '1', '" . $insertarr['ip'] . "', '" . $insertarr['name'] . "', '" . $insertarr['lastseen'] . "', '" . $insertarr['grpid'] . "', '" . $insertarr['nextup'] . "', '" . $insertarr['cldgroup'] . "', '" . $insertarr['platform'] . "', '" . $insertarr['nation'] . "', '" . $insertarr['version'] . "', '" . $insertarr['firstcon'] . "','1'),";
		}
		$allinsertdata = substr($allinsertdata, 0, -1);
		if ($allinsertdata != '') {
			if($mysqlcon->exec("INSERT INTO $dbname.user (uuid, cldbid, count, ip, name, lastseen, grpid, nextup, cldgroup, platform, nation, version, firstcon, online) VALUES $allinsertdata") === false) {
				echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
				$sqlerr++;
			}
		}
	}
	if ($debug == 'on') {
		echo '<br>allinsertdata:<br>', $allinsertdata, '<br><br>updatedata:<br><pre>', print_r($updatedata), '</pre><br>';
	}
	unset($insertdata);
	unset($allinsertdata);
	if ($updatedata != 0) {
		$allupdateuuid	 = '';
		$allupdatecldbid   = '';
		$allupdatecount	= '';
		$allupdateip	   = '';
		$allupdatename	 = '';
		$allupdatelastseen = '';
		$allupdategrpid	= '';
		$allupdatenextup   = '';
		$allupdateidle	 = '';
		$allupdatecldgroup = '';
		$allupdateboosttime = '';
		$allupdateplatform = '';
		$allupdatenation = '';
		$allupdateversion = '';
		foreach ($updatedata as $updatearr) {
			$allupdateuuid	 = $allupdateuuid . "'" . $updatearr['uuid'] . "',";
			$allupdatecldbid   = $allupdatecldbid . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['cldbid'] . "' ";
			$allupdatecount	= $allupdatecount . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['count'] . "' ";
			$allupdateip	   = $allupdateip . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['ip'] . "' ";
			$allupdatename	 = $allupdatename . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['name'] . "' ";
			$allupdatelastseen = $allupdatelastseen . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['lastseen'] . "' ";
			$allupdategrpid	= $allupdategrpid . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['grpid'] . "' ";
			$allupdatenextup   = $allupdatenextup . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['nextup'] . "' ";
			$allupdateidle	 = $allupdateidle . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['idle'] . "' ";
			$allupdatecldgroup = $allupdatecldgroup . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['cldgroup'] . "' ";
			$allupdateboosttime = $allupdateboosttime . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['boosttime'] . "' ";
			$allupdateplatform = $allupdateplatform . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['platform'] . "' ";
			$allupdatenation = $allupdatenation . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['nation'] . "' ";
			$allupdateversion = $allupdateversion . "WHEN '" . $updatearr['uuid'] . "' THEN '" . $updatearr['version'] . "' ";
		}
		$allupdateuuid = substr($allupdateuuid, 0, -1);
		if($mysqlcon->exec("UPDATE $dbname.user set cldbid = CASE uuid $allupdatecldbid END, count = CASE uuid $allupdatecount END, ip = CASE uuid $allupdateip END, name = CASE uuid $allupdatename END, lastseen = CASE uuid $allupdatelastseen END, grpid = CASE uuid $allupdategrpid END, nextup = CASE uuid $allupdatenextup END, idle = CASE uuid $allupdateidle END, cldgroup = CASE uuid $allupdatecldgroup END, boosttime = CASE uuid $allupdateboosttime END, platform = CASE uuid $allupdateplatform END, nation = CASE uuid $allupdatenation END, version = CASE uuid $allupdateversion END, online = 1 WHERE uuid IN ($allupdateuuid)") === false) {
			echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$sqlerr++;
		}
	}
	if ($debug == 'on') {
		echo '<br>allupdateuuid:<br>', $allupdateuuid, '<br>';
	}
	unset($updatedata);
	unset($allupdateuuid);
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
	echo '<br>', sprintf($lang['sitegen'], $buildtime, $sumentries), '<br>';
}
?>
</body>
</html>