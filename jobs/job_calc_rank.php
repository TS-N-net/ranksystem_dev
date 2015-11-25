<?PHP
$starttime = microtime(true);
$nowtime = time();
?>
<!doctype html>
<html>
<head>
  <title>TS-N.NET Ranksystem - Calc Rank</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="../other/style.css.php" />
<?PHP
require_once(substr(dirname(__FILE__),0,-4).'other/config.php');
require_once(substr(dirname(__FILE__),0,-4).'lang.php');
require_once(substr(dirname(__FILE__),0,-4).'ts3_lib/TeamSpeak3.php');


try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
	if (strlen($queryname)>27) $queryname = substr($queryname, 0, -3).'_nr'; else $queryname = $queryname .'_nr';
	if (strlen($queryname2)>26) $queryname2 = substr($queryname2, 0, -4).'_nr2'; else $queryname2 = $queryname2.'_nr2';
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

    // calc next rankup
	$upnextuptime = $nowtime - 86400;
    $uuidsoff = $mysqlcon->query("SELECT idle,count,grpid,cldgroup FROM $dbname.user WHERE online<>1 AND lastseen>$upnextuptime");
    if ($uuidsoff->rowCount() != 0) {
		
		$uuidsoff = $uuidsoff->fetchAll(PDO::FETCH_ASSOC);
        foreach($uuidsoff as $uuid) {
            $idle     = $uuid['idle'];
            $count    = $uuid['count'];
            $grpid    = $uuid['grpid'];
            $cldgroup = $uuid['cldgroup'];
            $sgroups  = explode(",", $cldgroup);
            if ($substridle == 1) {
                $activetime = $count - $idle;
                $dtF        = new DateTime("@0");
                $dtT        = new DateTime("@$activetime");
            } else {
                $activetime = $count;
                $dtF        = new DateTime("@0");
                $dtT        = new DateTime("@$count");
            }
            foreach ($grouptime as $time => $groupid) {
                if ($activetime > $time) {
                    $nextup = 0;
                } else {
                    $nextup = $time - $activetime;
                }
            }
			$updatenextup[] = array(
				"uuid" => $uuid['uuid'],
				"nextup" => $nextup
			);
        }
    }

    if (isset($updatenextup)) {
        $allupdateuuid   = '';
        $allupdatenextup = '';
        foreach ($updatenextup as $updatedata) {
            $allupdateuuid   = $allupdateuuid . "'" . $updatedata['uuid'] . "',";
            $allupdatenextup = $allupdatenextup . "WHEN '" . $updatedata['uuid'] . "' THEN '" . $updatedata['nextup'] . "' ";
        }
        $allupdateuuid = substr($allupdateuuid, 0, -1);
        if ($mysqlcon->exec("UPDATE $dbname.user set nextup = CASE uuid $allupdatenextup END WHERE uuid IN ($allupdateuuid)") === false) {
            echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
        }
    }
	if ($mysqlcon->exec("set @a:=0; UPDATE user u INNER JOIN (SELECT @a:=@a+1 nr,uuid FROM user ORDER BY count DESC) s USID (uuid) SET u.rank=s.nr;") === false) {
		echo '<span class="wncolor">',$mysqlcon->errorCode(),'</span><br>';
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