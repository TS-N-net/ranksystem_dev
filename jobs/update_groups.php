<?PHP
function update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$serverinfo) {
	$starttime = microtime(true);
	$sqlmsg = '';
	$sqlerr = 0;

    $sIconId = $serverinfo['virtualserver_icon_id'];
	$sIconId = ($sIconId < 0) ? (pow(2, 32)) - ($sIconId * -1) : $sIconId;
	$sIconFile = 0;
	if($sIconId > 600) {
		if ($slowmode == 1) sleep(1);
		try {
			$sIconFile = $ts3->iconDownload();
			file_put_contents(substr(dirname(__FILE__),0,-4) . "icons/servericon.png", $sIconFile);
		} catch (Exception $e) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$e->getCode(),': ',"Error by downloading Icon: ",$e->getMessage(),"\n";
			$sqlmsg .= $e->getCode() . ': ' . "Error by downloading Icon: " . $e->getMessage();
			$sqlerr++;
		}
	}
	
	// update groupinformations and download icons
    if(($dbgroups = $mysqlcon->query("SELECT * FROM $dbname.groups")) === false) {
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'],print_r($mysqlcon->errorInfo());
		$sqlmsg .= print_r($mysqlcon->errorInfo());
		$sqlerr++;
	}
    if ($dbgroups->rowCount() == 0) {
        $sqlhisgroup = "empty";
    } else {
		$servergroups = $dbgroups->fetchAll(PDO::FETCH_ASSOC);
        foreach($servergroups as $servergroup) {
            $sqlhisgroup[$servergroup['sgid']] = $servergroup['sgidname'];
        }
    }
	
	if ($slowmode == 1) sleep(1);
	try {
		$ts3groups = $ts3->serverGroupList();
	} catch (Exception $e) {
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$e->getCode(),': ',"Error by getting servergrouplist: ",$e->getMessage(),"\n";
		$sqlmsg .= $e->getCode() . ': ' . "Error by getting servergrouplist: " . $e->getMessage();
		$sqlerr++;
	}
	
    foreach ($ts3groups as $servergroup) {
		$tsgroupids[] = $servergroup['sgid'];
        $gefunden = 2;
        $iconid   = $servergroup['iconid'];
        $iconid   = ($iconid < 0) ? (pow(2, 32)) - ($iconid * -1) : $iconid;
		$iconfile = 0;
		if($iconid > 600) {
			$iconfile = $servergroup->iconDownload();
		}
        $sgname   = str_replace('\\', '\\\\', htmlspecialchars($servergroup['name'], ENT_QUOTES));
        if ($sqlhisgroup != "empty") {
            foreach ($sqlhisgroup as $sgid => $sname) {
                if ($sgid == $servergroup['sgid']) {
                    $gefunden       = 1;
                    $updategroups[] = array(
                        "sgid" => $servergroup['sgid'],
                        "sgidname" => $sgname,
                        "iconid" => $iconid,
						"icon" => $iconfile
                    );
                    break;
                }
            }
            if ($gefunden != 1) {
                $insertgroups[] = array(
                    "sgid" => $servergroup['sgid'],
                    "sgidname" => $sgname,
                    "iconid" => $iconid,
					"icon" => $iconfile
                );
            }
        } else {
            $insertgroups[] = array(
                "sgid" => $servergroup['sgid'],
                "sgidname" => $sgname,
                "iconid" => $iconid,
				"icon" => $iconfile
            );
        }
    }

    if (isset($insertgroups)) {
        $allinsertdata = '';
        foreach ($insertgroups as $insertarr) {
            $allinsertdata = $allinsertdata . "('" . $insertarr['sgid'] . "', '" . $insertarr['sgidname'] . "', '" . $insertarr['iconid'] . "'),";
			if($insertarr['iconid']!=0 && $updatedata['iconid']>300) {
				file_put_contents(substr(dirname(__FILE__),0,-4) . "icons/" . $insertarr['sgid'] . ".png", $insertarr['icon']);
			}
        }
        $allinsertdata = substr($allinsertdata, 0, -1);
        if ($allinsertdata != '') {
            if($mysqlcon->exec("INSERT INTO $dbname.groups (sgid, sgidname, iconid) VALUES $allinsertdata") === false) {
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'],print_r($mysqlcon->errorInfo());
				$sqlmsg .= print_r($mysqlcon->errorInfo());
				$sqlerr++;
			}
        }
    }

    if (isset($updategroups)) {
        $allsgids        = '';
        $allupdatesgid   = '';
		$allupdateiconid = '';
        foreach ($updategroups as $updatedata) {
            $allsgids        = $allsgids . "'" . $updatedata['sgid'] . "',";
            $allupdatesgid   = $allupdatesgid . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['sgidname'] . "' ";
            $allupdateiconid = $allupdateiconid . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['iconid'] . "' ";
			if($updatedata['iconid']!=0 && $updatedata['iconid']>300) {
				file_put_contents(substr(dirname(__FILE__),0,-4) . "icons/" . $updatedata['sgid'] . ".png", $updatedata['icon']);
			}
        }
        $allsgids = substr($allsgids, 0, -1);
        if($mysqlcon->exec("UPDATE $dbname.groups set sgidname = CASE sgid $allupdatesgid END, iconid = CASE sgid $allupdateiconid END WHERE sgid IN ($allsgids)") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'],print_r($mysqlcon->errorInfo());
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
    }
	
	foreach ($sqlhisgroup as $sgroupid => $sgroupname) {
		if(!in_array($sgroupid, $tsgroupids)) {
			$delsgroupids = $delsgroupids . "'" . $sgroupid . "',";
		}
	}
	
	if(isset($delsgroupids)) {
		$delsgroupids = substr($delsgroupids, 0, -1);
		if($mysqlcon->exec("DELETE FROM groups WHERE sgid IN ($delsgroupids)") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'],print_r($mysqlcon->errorInfo());
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
	}

	if ($sqlerr == 0) {
		if(isset($_SERVER['argv'][1])) {
			$jobid = $_SERVER['argv'][1];
			if($mysqlcon->exec("UPDATE $dbname.job_log SET status='0', runtime='$buildtime' WHERE id='$jobid'") === false) {
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'],print_r($mysqlcon->errorInfo());
			}
		}
	} else {
		if(isset($_SERVER['argv'][1])) {
			$jobid = $_SERVER['argv'][1];
			if($mysqlcon->exec("UPDATE $dbname.job_log SET status='1', err_msg='$sqlmsg', runtime='$buildtime' WHERE id='$jobid'") === false) {
				echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'],print_r($mysqlcon->errorInfo());
			}
		}
	}
}
?>