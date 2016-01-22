#!/usr/bin/php
<?PHP
set_time_limit(0);
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'UTF-8');
date_default_timezone_set('Europe/Berlin');
error_reporting(0);

echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),"Initialize Bot...";
require_once(substr(dirname(__FILE__),0,-4).'other/config.php');
require_once(substr(dirname(__FILE__),0,-4).'lang.php');
require_once(substr(dirname(__FILE__),0,-4).'ts3_lib/TeamSpeak3.php');
require_once(substr(dirname(__FILE__),0,-4).'jobs/calc_user.php');
require_once(substr(dirname(__FILE__),0,-4).'jobs/get_avatars.php');
require_once(substr(dirname(__FILE__),0,-4).'jobs/update_groups.php');
require_once(substr(dirname(__FILE__),0,-4).'jobs/calc_serverstats.php');
require_once(substr(dirname(__FILE__),0,-4).'jobs/calc_userstats.php');
require_once(substr(dirname(__FILE__),0,-4).'jobs/clean.php');
echo " finished\n";

echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),"Connect to TS3 Server...";
try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice'] . "&blocking=0");
	echo " finished\n";
	//$ts3->notifyRegister("textprivate");
	//TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", "onTextMessageEvent");
	
    try {
        $ts3->selfUpdate(array('client_nickname' => "RankSystem"));
    }
    catch (Exception $e) {
        try {
            $ts3->selfUpdate(array('client_nickname' => "Ranksystem"));
        }
        catch (Exception $e) {
            echo "\n",DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'], $e->getCode(), ': ', $e->getMessage(),"\n";
        }
    }
	echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),"Join to specified Channel...";
	$whoami = $ts3->whoami();
	try {
		$ts3->clientMove($whoami['client_id'],189152);
	} catch (Exception $e) {
		echo "\n",DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'], $e->getCode(), ': ', $e->getMessage(),"\n";
	}
	echo " finished\n";

	echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),"Bot starts now his work!\n";
	while(1) {
		$allclients = $ts3->clientList();
		$serverinfo = $ts3->serverInfo();

		try { $ts3->clientMove($whoami['client_id'],189152); } catch (Exception $e) {}
		calc_user($ts3,$mysqlcon,$lang,$dbname,$slowmode,$showgen,$update,$grouptime,$boostarr,$resetbydbchange,$msgtouser,$uniqueid,$updateinfotime,$currvers,$substridle,$exceptuuid,$exceptgroup,$allclients);
		get_avatars($ts3,$mysqlcon,$lang,$dbname,$slowmode);
		update_groups($ts3,$mysqlcon,$lang,$dbname,$slowmode,$serverinfo);
		calc_serverstats($ts3,$mysqlcon,$lang,$dbname,$slowmode,$serverinfo,$substridle,$grouptime);
		calc_userstats($ts3,$mysqlcon,$lang,$dbname,$slowmode);
		clean($ts3,$mysqlcon,$lang,$dbname,$slowmode,$cleanclients,$cleanperiod);
	}
}
catch (Exception $e) {
    echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'] . $e->getCode() . ': ' . $e->getMessage(),"\n";
	$offline_status = array(110,257,258,1024,1026,1031,1032,1033,1034,1280,1793);
	if(in_array($e->getCode(), $offline_status)) {
		if($mysqlcon->exec("UPDATE $dbname.stats_server SET server_status='0'") === false) {
			echo DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))->setTimeZone(new DateTimeZone('Europe/Berlin'))->format("Y-m-d H:i:s.u "),$lang['error'],print_r($mysqlcon->errorInfo()),"\n";
			$sqlmsg .= print_r($mysqlcon->errorInfo());
			$sqlerr++;
		}
	}
	$sqlmsg .= $e->getCode() . ': ' . $e->getMessage();
	$sqlerr++;
}
?>