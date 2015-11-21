<?PHP
$starttime = microtime(true);
?>
<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem - Update Groups</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="../other/style.css.php" />
<?PHP
require_once(substr(dirname(__FILE__),0,-4).'other/config.php');
require_once(substr(dirname(__FILE__),0,-4).'lang.php');
require_once(substr(dirname(__FILE__),0,-4).'ts3_lib/TeamSpeak3.php');

try {
    $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
    $nowtime           = time();
	if(strlen($queryname)>27) {
		$queryname = substr($queryname, 0, -3).'_ug';
	} else {
		$queryname = $queryname .'_ug';
	}
	if(strlen($queryname2)>26) {
		$queryname2 = substr($queryname2, 0, -4).'_ug2';
	} else {
		$queryname2 = $queryname2.'_ug2';
	}
    if ($slowmode == 1)
        sleep(1);
    try {
        $ts3_VirtualServer->selfUpdate(array(
            'client_nickname' => $queryname
        ));
    }
    catch (Exception $e) {
        if ($slowmode == 1)
            sleep(1);
        try {
            $ts3_VirtualServer->selfUpdate(array(
                'client_nickname' => $queryname2
            ));
            echo $lang['queryname'], '<br><br>';
        }
        catch (Exception $e) {
            echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
        }
    }
	
	// update groupinformations and download icons
    $dbgroups = $mysqlcon->query("SELECT * FROM $dbname.groups");
    if ($dbgroups->rowCount() == 0) {
        $sqlhisgroup = "empty";
    } else {
		$servergroups = $dbgroups->fetchAll(PDO::FETCH_ASSOC);
        foreach($servergroups as $servergroup) {
            $sqlhisgroup[$servergroup['sgid']] = $servergroup['sgidname'];
        }
    }
	
	if ($slowmode == 1) sleep(1);
    $ts3groups   = $ts3_VirtualServer->serverGroupList();
	
    foreach ($ts3groups as $servergroup) {
        $gefunden = 2;
        $iconid   = $servergroup['iconid'];
        $iconid   = ($iconid < 0) ? (pow(2, 32)) - ($iconid * -1) : $iconid;
		$iconfile = 0;
		if($iconid > 300) {
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
            if ($mysqlcon->exec("INSERT INTO $dbname.groups (sgid, sgidname, iconid) VALUES $allinsertdata") === false) {
                echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
            }
        }
    }
	
    unset($insertgroups);
    unset($allinsertdata);
	

    if (isset($updategroups)) {
        $allsgids        = '';
        $allupdatesgid   = '';
		$allupdateiconid = '';
        foreach ($updategroups as $updatedata) {
            $allsgids        = $allsgids . "'" . $updatedata['sgid'] . "',";
            $allupdatesgid   = $allupdatesgid . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['sgidname'] . "' ";
            $allupdateiconid = $allupdateiconid . "WHEN '" . $updatedata['sgid'] . "' THEN '" . $updatedata['iconid'] . "' ";
			if($updatedata['iconid']!=0 && $updatedata['iconid']>300) {
				file_put_contents(substr(dirname(__FILE__),0,-4). . "icons/" . $updatedata['sgid'] . ".png", $updatedata['icon']);
			}
        }
        $allsgids = substr($allsgids, 0, -1);
        if ($mysqlcon->exec("UPDATE $dbname.groups set sgidname = CASE sgid $allupdatesgid END, iconid = CASE sgid $allupdateiconid END WHERE sgid IN ($allsgids)") === false) {
            echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
        }
    }
	unset($updatedata);
    unset($allsgids);
    unset($allupdatesgid);
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