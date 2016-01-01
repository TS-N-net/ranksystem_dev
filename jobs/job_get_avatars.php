#!/usr/bin/php
<?PHP
set_time_limit(60);
$starttime = microtime(true);

require_once(substr(dirname(__FILE__),0,-4).'other/config.php');
require_once(substr(dirname(__FILE__),0,-4).'lang.php');
require_once(substr(dirname(__FILE__),0,-4).'ts3_lib/TeamSpeak3.php');

$sqlerr = 0;
$count = 0;

try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
	if (strlen($queryname)>27) $queryname = substr($queryname, 0, -3).'_av'; else $queryname = $queryname .'_cc';
	if (strlen($queryname2)>26) $queryname2 = substr($queryname2, 0, -4).'_av2'; else $queryname2 = $queryname2.'_cc2';
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
			$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
			$sqlerr++;
        }
    }

	$tsfilelist = $ts3->channelFileList($cid="0", $cpw="", $path="/");
	$fsfilelist = opendir(substr(dirname(__FILE__),0,-4).'other/avatars/');
	while (false !== ($fsfile = readdir($fsfilelist))) {
		if ($fsfile != '.' && $fsfile != '..') {
			$fsfilelistarray[$fsfile] = filemtime(substr(dirname(__FILE__),0,-4).'other/avatars/'.$fsfile);
		}
    }

	foreach($tsfilelist as $tsfile) {
		$fullfilename = '/'.$tsfile['name'];
		$uuidasbase16 = substr($tsfile['name'],7);
		if (!isset($fsfilelistarray[$uuidasbase16]) || $tsfile['datetime']>$fsfilelistarray[$uuidasbase16]) {
			if (substr($tsfile['name'],0,7) == 'avatar_') {
				try {
					$avatar = $ts3->transferInitDownload($clientftfid="5",$cid="0",$name=$fullfilename,$cpw="", $seekpos=0);
					$transfer = TeamSpeak3::factory("filetransfer://" . $avatar["host"] . ":" . $avatar["port"]);
					$tsfile = $transfer->download($avatar["ftkey"], $avatar["size"]);
					$avatarfilepath	= substr(dirname(__FILE__),0,-4).'other/avatars/'.$uuidasbase16;
					echo "Download avatar: ",$fullfilename,"\n";
					file_put_contents($avatarfilepath, $tsfile);
					$count++;
				}
				catch (Exception $e) {
					echo $lang['error'] . $e->getCode() . ': ' . $e->getMessage();
					$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
					$sqlerr++;
				}
			}
		}
	}
	if ($count == 0) {
		echo "Nothing to do.. All avatars already downloaded and are up to date\n";
	}
}
catch (Exception $e) {
    echo $lang['error'] . $e->getCode() . ': ' . $e->getMessage();
	$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
	$sqlerr++;
}

if ($showgen == 1) {
    $buildtime = microtime(true) - $starttime;
    echo "\n",sprintf($lang['sitegen'], $buildtime, $count),"\n";
}

if ($sqlerr == 0) {
	if(isset($_SERVER['argv'][1])) {
		$jobid = $_SERVER['argv'][1];
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='0', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo $lang['error'],print_r($mysqlcon->errorInfo());
		}
	}
} else {
	if(isset($_SERVER['argv'][1])) {
		$jobid = $_SERVER['argv'][1];
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='1', err_msg='$sqlmsg', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo $lang['error'],print_r($mysqlcon->errorInfo());
		}
	}
}
?>