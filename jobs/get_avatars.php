<?PHP
function get_avatars($ts3,$mysqlcon,$lang,$dbname,$slowmode,$jobid) {
	$starttime = microtime(true);
	$sqlmsg = '';
	$sqlerr = 0;
	$count = 0;

	if ($slowmode == 1) sleep(1);
	try {
		$tsfilelist = $ts3->channelFileList($cid="0", $cpw="", $path="/");
	} catch (Exception $e) {
		echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),"get_avatars 1:",$e->getCode(),': ',"Error by getting Avatarlist: ",$e->getMessage(),"\n";
		$sqlmsg .= $e->getCode() . ': ' . "Error by getting Avatarlist: " . $e->getMessage();
		$sqlerr++;
	}
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
				if ($slowmode == 1) sleep(1);
				try {
					$avatar = $ts3->transferInitDownload($clientftfid="5",$cid="0",$name=$fullfilename,$cpw="", $seekpos=0);
					$transfer = TeamSpeak3::factory("filetransfer://" . $avatar["host"] . ":" . $avatar["port"]);
					$tsfile = $transfer->download($avatar["ftkey"], $avatar["size"]);
					$avatarfilepath	= substr(dirname(__FILE__),0,-4).'other/avatars/'.$uuidasbase16;
					echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),"Download avatar: ",$fullfilename,"\n";
					file_put_contents($avatarfilepath, $tsfile);
					$count++;
				}
				catch (Exception $e) {
					echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),"get_avatars 2:",$e->getCode(),': ',"Error by download Avatar: ",$e->getMessage(),"\n";
					$sqlmsg .= $e->getCode() . ': ' . "Error by download Avatar: " . $e->getMessage();
					$sqlerr++;
				}
			}
		}
	}
	
	$buildtime = microtime(true) - $starttime;

	if ($sqlerr == 0) {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='0', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),"get_avatars 3:",print_r($mysqlcon->errorInfo());
		}
	} else {
		if($mysqlcon->exec("UPDATE $dbname.job_log SET status='1', err_msg='$sqlmsg', runtime='$buildtime' WHERE id='$jobid'") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),"get_avatars 4:",print_r($mysqlcon->errorInfo());
		}
	}
}
?>