<?PHP
ob_start();
?>
<!doctype html>
<html>
<head>
  <title>TS-N.NET ranksystem - Update 1.00</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="other/style.css.php" />
</head>  
<body>
<?php
require_once('other/config.php');
require_once('lang.php');
$dbname=$db['dbname'];


if($currvers=='0.13-beta') {
	echo'<span class="wncolor">'.$lang['alrup'].'</span><br>';
	if(is_file('install.php') or glob('update*.php')) {
		unlink('install.php');
		$unlinkfiles = glob('update*.php');
		// if(array_map('unlink',$unlinkfiles) === true) {
		echo '<span class="wncolor">'.sprintf($lang['updel'],'install.php<br>update*.php<br>').'</span>';
		$redurl = 'http://'.$_SERVER["HTTP_HOST"].str_replace(str_replace($_SERVER['PHP_SELF'], '', $_SERVER['SCRIPT_FILENAME']), '', __DIR__).'/webinterface.php';
		header("Location: " . $redurl);
	}
} elseif (!is_writable('./other/dbconfig.php') || substr(sprintf('%o', fileperms('./icons/')), -4)!='0777' || substr(sprintf('%o', fileperms('./other/avatars/')), -4)!='0777') {
	echo '<span class="wncolor">',$lang['isntwichm'],'</span>';
} else {
	echo sprintf($lang['updb'],'1.00','1-00');
	echo '<form name="updateranksystem" method="post"><input type="submit" name="updateranksystem" value="update"></form>';
}

if(isset($_POST['updateranksystem'])) {
	$errcount = 1;
	if($mysqlcon->exec("ALTER TABLE $dbname.user ADD (boosttime bigint(11) NOT NULL default '0', rank bigint(11) NOT NULL default '0', platform text default NULL, nation text default NULL, version text default NULL, firstcon bigint(11) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("ALTER TABLE $dbname.config ADD (boost text default NULL, showcolas int(1) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("CREATE TABLE $dbname.server_usage (timestamp bigint(11) NOT NULL default '0', clients bigint(11) NOT NULL default '0', channel bigint(11) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("CREATE TABLE $dbname.user_snapshot (timestamp bigint(11) NOT NULL default '0', uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci, count bigint(11) NOT NULL default '0', idle bigint(11) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("CREATE INDEX snapshot_timestamp ON $dbname.user_snapshot (timestamp)") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("CREATE TABLE $dbname.stats_server (total_user bigint(11) NOT NULL default '0', total_online_time bigint(13) NOT NULL default '0', total_online_month bigint(11) NOT NULL default '0', total_online_week bigint(11) NOT NULL default '0', total_active_time bigint(11) NOT NULL default '0', total_inactive_time bigint(11) NOT NULL default '0', country_nation_name_1 varchar(3) NOT NULL default '0', country_nation_name_2 varchar(3) NOT NULL default '0', country_nation_name_3 varchar(3) NOT NULL default '0', country_nation_name_4 varchar(3) NOT NULL default '0', country_nation_name_5 varchar(3) NOT NULL default '0', country_nation_1 bigint(11) NOT NULL default '0', country_nation_2 bigint(11) NOT NULL default '0', country_nation_3 bigint(11) NOT NULL default '0', country_nation_4 bigint(11) NOT NULL default '0', country_nation_5 bigint(11) NOT NULL default '0', country_nation_other bigint(11) NOT NULL default '0', platform_1 bigint(11) NOT NULL default '0', platform_2 bigint(11) NOT NULL default '0', platform_3 bigint(11) NOT NULL default '0', platform_4 bigint(11) NOT NULL default '0', platform_5 bigint(11) NOT NULL default '0', platform_other bigint(11) NOT NULL default '0', version_name_1 varchar(35) NOT NULL default '0', version_name_2 varchar(35) NOT NULL default '0', version_name_3 varchar(35) NOT NULL default '0', version_name_4 varchar(35) NOT NULL default '0', version_name_5 varchar(35) NOT NULL default '0', version_1 bigint(11) NOT NULL default '0', version_2 bigint(11) NOT NULL default '0', version_3 bigint(11) NOT NULL default '0', version_4 bigint(11) NOT NULL default '0', version_5 bigint(11) NOT NULL default '0', version_other bigint(11) NOT NULL default '0', server_status int(1) NOT NULL default '0', server_free_slots bigint(11) NOT NULL default '0', server_used_slots bigint(11) NOT NULL default '0', server_channel_amount bigint(11) NOT NULL default '0', server_ping bigint(11) NOT NULL default '0', server_packet_loss float (4,4), server_bytes_down bigint(11) NOT NULL default '0', server_bytes_up bigint(11) NOT NULL default '0', server_uptime bigint(11) NOT NULL default '0', server_id bigint(11) NOT NULL default '0', server_name text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_pass int(1) NOT NULL default '0', server_creation_date bigint(11) NOT NULL default '0', server_platform text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_weblist text CHARACTER SET utf8 COLLATE utf8_unicode_ci, server_version text CHARACTER SET utf8 COLLATE utf8_unicode_ci)") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("CREATE TABLE $dbname.stats_user (uuid varchar(29) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, removed int(1) NOT NULL default '0', rank bigint(11) NOT NULL default '0', total_connections bigint(11) NOT NULL default '0', count_week bigint(11) NOT NULL default '0', count_month bigint(11) NOT NULL default '0', idle_week bigint(11) NOT NULL default '0', idle_month bigint(11) NOT NULL default '0', achiev_count bigint(11) NOT NULL default '0', achiev_time bigint(11) NOT NULL default '0', achiev_connects bigint(11) NOT NULL default '0', achiev_battles bigint(11) NOT NULL default '0', achiev_time_perc int(3) NOT NULL default '0', achiev_connects_perc int(3) NOT NULL default '0', achiev_battles_perc int(3) NOT NULL default '0', battles_total bigint(11) NOT NULL default '0', battles_won bigint(11) NOT NULL default '0', battles_lost bigint(11) NOT NULL default '0', client_description text CHARACTER SET utf8 COLLATE utf8_unicode_ci, base64hash varchar(58) CHARACTER SET utf8 COLLATE utf8_unicode_ci, client_total_up bigint(15) NOT NULL default '0', client_total_down bigint(15) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("INSERT INTO $dbname.stats_server SET total_user='9999'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("CREATE TABLE $dbname.job_check (job_name varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci PRIMARY KEY, timestamp bigint(11) NOT NULL default '0')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("INSERT INTO $dbname.job_check (job_name) VALUES ('calc_user_limit'),('calc_user_lastscan'),('check_update'),('check_clean')") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($mysqlcon->exec("CREATE TABLE $dbname.job_log (id bigint(11) AUTO_INCREMENT PRIMARY KEY, timestamp bigint(11) NOT NULL default '0', job_name varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci, status int(1) NOT NULL default '0', err_msg text CHARACTER SET utf8 COLLATE utf8_unicode_ci, runtime float (4,4))") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($lastscan = $mysqlcon->query("SELECT timestamp FROM $dbname.lastscan") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	$timestampls = $lastscan->fetchAll();
	$calc_user_lastscan = $timestampls[0]['timestamp'];
	if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$calc_user_lastscan' WHERE job_name='calc_user_lastscan'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	} elseif($mysqlcon->exec("DROP TABLE $dbname.lastscan") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($lastupdate = $mysqlcon->query("SELECT timestamp FROM $dbname.upcheck") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	$timestampuc = $lastupdate->fetchAll();
	$check_update = $timestampuc[0]['timestamp'];
	if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$check_update' WHERE job_name='check_update'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	} elseif($mysqlcon->exec("DROP TABLE $dbname.upcheck") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if($lastclean = $mysqlcon->query("SELECT timestamp FROM $dbname.cleanclients") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	$timestamplc = $lastclean->fetchAll();
	$check_clean = $timestampls[0]['timestamp'];
	if($mysqlcon->exec("UPDATE $dbname.job_check SET timestamp='$check_clean' WHERE job_name='check_clean'") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	} elseif($mysqlcon->exec("DROP TABLE $dbname.cleanclients") === false) {
		echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
		$errcount++;
	}
	if ($errcount == 1) {
		if($mysqlcon->exec("UPDATE $dbname.config set currvers='1.00'") === false) {
			echo $lang['insttberr'].'<span class="wncolor">'.print_r($mysqlcon->errorInfo()).'.</span>';
			$errcount++;
		}
		if ($errcount == 1) {
			echo'<span class="sccolor"">'.$lang['upsucc'].'</span><br><br>';
			if(is_file('install.php') or glob('update*.php')) {
				unlink('install.php');
				$unlinkfiles = glob('update*.php');
				// if(array_map('unlink',$unlinkfiles) === true) {
				echo '<span class="wncolor">'.sprintf($lang['updel'],'install.php<br>update*.php<br>').'</span>';
				$redurl = 'http://'.$_SERVER["HTTP_HOST"].str_replace(str_replace($_SERVER['PHP_SELF'], '', $_SERVER['SCRIPT_FILENAME']), '', __DIR__).'/webinterface.php';
				header("Location: " . $redurl);
			}
		}
	}
	if ($errcount > 1) {
		echo "<span class=\"wncolor\">Error by Updating the Database for the Ranksystem. Please run the following SQL Statements yourself and be sure all works correctly:</span><br><br>
		ALTER TABLE user ADD (boosttime bigint(11) NOT NULL default '0')<br>
		ALTER TABLE config ADD (boost text default NULL)<br>
		ALTER TABLE config ADD (showcolas int(1) NOT NULL default '0')<br>
		";
	}
}
?>
</body>
</html>
<?PHP
ob_end_flush();
?>