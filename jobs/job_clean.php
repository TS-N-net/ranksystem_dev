<?PHP
$starttime = microtime(true);
$count_tsuser['count'] = 0;

require_once('other/config.php');
require_once('lang.php');
require_once('ts3_lib/TeamSpeak3.php');

try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
    $nowtime           = time();
	if(strlen($queryname)>27) {
		$queryname = substr($queryname, 0, -3).'_cc';
	} else {
		$queryname = $queryname .'_cc';
	}
	if(strlen($queryname2)>26) {
		$queryname2 = substr($queryname2, 0, -4).'_cc2';
	} else {
		$queryname2 = $queryname2.'_cc2';
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
	
	// clean old clients out of the database
	if ($cleanclients == 1 && $slowmode != 1) {
		$cleantime = $nowtime - $cleanperiod;
		$lastclean = $mysqlcon->query("SELECT * FROM $dbname.cleanclients");
		$lastclean = $lastclean->fetchAll();
		$dbuserdata = $mysqlcon->query("SELECT uuid FROM $dbname.user");
		$countrs = $dbuserdata->rowCount();
		$uuids = $dbuserdata->fetchAll();
		if ($lastclean[0]['timestamp'] < $cleantime) {
			echo '<span class="hdcolor"><b>', $lang['clean'], '</b></span><br>';
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
			echo sprintf($lang['cleants'], $countts, $count_tsuser['count']),'<br>';
			echo sprintf($lang['cleanrs'], $countrs),'<br>';

			if(isset($deleteuuids)) {
				$alldeldata = '';
				foreach ($deleteuuids as $dellarr) {
					$alldeldata = $alldeldata . "'" . $dellarr . "',";
				}
				$alldeldata = substr($alldeldata, 0, -1);
				$alldeldata = "(".$alldeldata.")";
				if ($alldeldata != '') {
					if ($mysqlcon->exec("DELETE FROM $dbname.user WHERE uuid IN $alldeldata") === false) {
						echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
					} else {
						echo '<span class="sccolor">',sprintf($lang['cleandel'], $countdel),'</span><br>';
						if ($mysqlcon->exec("UPDATE $dbname.cleanclients SET timestamp='$nowtime'") === false) {
							echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
						}
					}
				}
			} else {
				echo '<span class="ifcolor">',$lang['cleanno'],'</span><br>';
				if ($mysqlcon->exec("UPDATE $dbname.cleanclients SET timestamp='$nowtime'") === false) {
					echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
				}
			}
		}
	}
}
catch (Exception $e) {
    echo $lang['error'] . $e->getCode() . ': ' . $e->getMessage();
}
if ($showgen == 1) {
    $buildtime = microtime(true) - $starttime;
    echo '<br>', sprintf($lang['sitegen'], $buildtime, $count_tsuser['count']), '<br>';
}
?>