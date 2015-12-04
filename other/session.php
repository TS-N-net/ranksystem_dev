<?PHP
if(isset($_POST['refresh'])) {
    $_SESSION = array();
    session_destroy();
}

function set_session_ts3($hpclientip, $ts3) {
	$allclients = $ts3->clientList();
	$_SESSION['connected']						= 0;
	foreach ($allclients as $client) {
		$tsip									= ip2long($client['connection_client_ip']);
		if ($hpclientip == $tsip) {
			$_SESSION['tsuid']					= htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
			$_SESSION['tscldbid']				= $client['client_database_id'];
			$_SESSION['tsname']					= str_replace('\\', '\\\\', htmlspecialchars($client['client_nickname'], ENT_QUOTES));
			$_SESSION['tscreated']				= date('d-m-Y',$client['client_created']);
			//$_SESSION['tsgroups']				= $client['client_servergroups'];
			$_SESSION['tsconnections']			= $client['client_totalconnections'];
			$_SESSION['serverport']				= $ts3['virtualserver_port'];
			if ($client['client_flag_avatar'] != NULL) {
				$client_avatar_flag				= $client['client_flag_avatar']->toString();
				$_SESSION['tsavatarfile']		= $client->avatarDownload();
				$_SESSION['tsavatar']			= $client_avatar_flag;
				$avatarfilepath					= '../other/avatars/'.$client_avatar_flag;
				file_put_contents($avatarfilepath, $_SESSION['tsavatarfile']);
			} else {
				$_SESSION['tsavatar']			= "none";
			}
			$_SESSION['connected']				= 1;
			break;
		}
	}
}
?>