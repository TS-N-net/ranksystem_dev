<?PHP
session_start();
$starttime = microtime(true);

require_once('../other/config.php');
require_once('../ts3_lib/TeamSpeak3.php');
require_once('../lang.php');
require_once('../other/session.php');

if(!isset($_SESSION['tsuid']) && !isset($_SESSION['tserror'])) {
	try {
		$ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
		if (strlen($queryname)>27) $queryname = substr($queryname, 0, -3).'_st'; else $queryname = $queryname .'_st';
		if (strlen($queryname2)>26) $queryname2 = substr($queryname2, 0, -4).'_st2'; else $queryname2 = $queryname2.'_st2';
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

		$hpclientip = ip2long($_SERVER['REMOTE_ADDR']);
		if ($slowmode == 1) sleep(1);
		set_session_ts3($hpclientip, $ts3);
	}
	catch (Exception $e) {
		echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
		$offline_status = array(110,257,258,1024,1026,1031,1032,1033,1034,1280,1793);
		if(in_array($e->getCode(), $offline_status)) {
			$_SESSION['tserror'] = "offline";
		}
	}
}
require_once('nav.php');
?>
        <div id="page-wrapper">

            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            Ranksystem Information
                        </h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <h4><strong><font color="#337ab7">What Is The Ranksystem?</font></strong></h4>
                        <p>A TS3 Bot, which gathers information and statistics about every user and displays the result on <strong>this</strong> site and the <a href="test.php"><u><font color="#000000">List Rankup</font></u></a>.
                        </p>
                        <br>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <h4><strong><font color="#5cb85c">Who Created The Ranksystem?</font></strong></h4>
                        <p>The Ranksystem was coded by <strong>Newcomer1989</strong> from <a href="http://ts-n.net/" target="_blank"><u><font color="#000000">TS-N.NET</font></u></a> in cooperation with <strong>Benjamin Frost.</strong></p>
                        <p>This Site was coded by <strong>Benjamin Frost</strong> in a cooperation with <strong>Newcomer1989.</strong></p>
                        <br>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <h4><strong><font color="#f0ad4e">When Did You Create The Ranksystem?</font></strong></h4>
                        <p>First alpha release: 05/10/2014 (dd/mm/yyyy).</p>
                        <p>First beta release: 01/02/2015 (dd/mm/yyyy).</p>
                        <p>You can see the newest version on the <a href="http://ts-n.net/ranksystem.php" target="_blank"><u><font color="#000000">Ranksystem Website</font></u></a>.</p>
                        <br>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <h4><strong><font color="#d9534f">How Did You Create The Ranksystem?</font></strong></h4>
                        <p>The Ranksystem is coded in PHP with the TS3 API.</p>
                        <p>This Website is coded with HTML (Bootstrap CSS), PHP and some small Javascripts.</p>
                        <br>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
</body>

</html>