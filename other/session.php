<?PHP
if(isset($_POST['refresh'])) {
	$_SESSION = array();
	session_destroy();
}

function set_session_ts3($hpclientip, $ts3, $voiceport) {
	$allclients = $ts3->clientList();
	$_SESSION['connected']						= 0;
	$_SESSION['serverport']						= $voiceport;
	foreach ($allclients as $client) {
		$tsip									= ip2long($client['connection_client_ip']);
		if ($hpclientip == $tsip) {
			$_SESSION['tsuid']					= $client['client_unique_identifier']->toString();
			$_SESSION['tscldbid']				= $client['client_database_id'];
			$_SESSION['tsname']					= $client['client_nickname']->toString();
			$_SESSION['tscreated']				= date('d-m-Y',$client['client_created']);
			//$_SESSION['tsgroups']				= $client['client_servergroups'];
			$_SESSION['tsconnections']			= $client['client_totalconnections'];
			$_SESSION['serverport']				= $ts3['virtualserver_port'];
			$convert = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p');
			$uuidasbase16 = '';
			for ($i = 0; $i < 20; $i++) {
				$char = ord(substr(base64_decode($_SESSION['tsuid']), $i, 1));
				$uuidasbase16 .= $convert[($char & 0xF0) >> 4];
				$uuidasbase16 .= $convert[$char & 0x0F];
			}
			if(is_file('../other/avatars/'.$uuidasbase16)) {
				$_SESSION['tsavatar']			= $uuidasbase16;
			} else {
				$_SESSION['tsavatar']			= "none";
			}
			$_SESSION['connected']				= 1;
			break;
		}
	}
}
?>