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

$sqlerr = 0;

// calc next rankup
$upnextuptime = $nowtime - 86400;
if(($uuidsoff = $mysqlcon->query("SELECT uuid,idle,count,grpid,cldgroup FROM $dbname.user WHERE online<>1 AND lastseen>$upnextuptime")) === false) {
	echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
	$sqlerr++;
}
$total_user = $uuidsoff->rowCount();
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
		echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$sqlerr++;
	}
}
if($mysqlcon->exec("SET @a:=0") === false) {
	echo $lang['error'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
	$sqlerr++;
}
if($mysqlcon->exec("UPDATE $dbname.user u INNER JOIN (SELECT @a:=@a+1 nr,uuid FROM $dbname.user ORDER BY count DESC) s USING (uuid) SET u.rank=s.nr") === false) {
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