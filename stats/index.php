<?PHP
session_start();
require_once('../other/config.php');
require_once('../ts3_lib/TeamSpeak3.php');
require_once('../lang.php');
require_once('../other/session.php');

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

    if(!isset($_SESSION['tsuid'])) {
		$hpclientip = ip2long($_SERVER['REMOTE_ADDR']);
        if ($slowmode == 1) sleep(1);
		set_session_ts3($hpclientip, $ts3);
    }
}
catch (Exception $e) {
    echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
}

$sql = $mysqlcon->query("SELECT * FROM $dbname.stats_server");
$sql_res = $sql->fetchAll();

$server_usage_sql = $mysqlcon->query("SELECT * FROM $dbname.server_usage ORDER BY(timestamp) DESC LIMIT 0, 47");
$server_usage_sql_res = $server_usage_sql->fetchAll();

if(isset($_GET['usage'])) {
	if ($_GET["usage"] == 'week') {
		$usage = 'week';
	} elseif ($_GET["usage"] == 'month') {
		$usage = 'month';
	} else {
		$usage = 'day';
	}
} else {
	$usage = 'day';
}

echo $usage;
?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../icons/rs.png">

    <title>TS-N.NET Ranksystem</title>

    <!-- Bootstrap Core CSS -->
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../bootstrap/addons/sb-admin.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="../bootstrap/addons/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../bootstrap/addons/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- jQuery -->
    <script src="../bootstrap/js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../bootstrap/js/bootstrap.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="../bootstrap/addons/js-plugins/morris/raphael.min.js"></script>
    <script src="../bootstrap/addons/js-plugins/morris/morris.min.js"></script>
    <script src="../bootstrap/addons/js-plugins/morris/morris-data.js"></script>
</head>

<body>
    <div id="myModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Server News</h4>
                </div>
                <div class="modal-body">
                    <p>Example Server News Text</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div id="myModal2" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Refresh Client Information</h4>
                </div>
                <div class="modal-body">
                    <p>Only use this Refresh, when your TS3 information got changed, such as your TS3 username</p>
                    <p>It only works, when you are connected to the TS3 Server at the same time</p>
                </div>
                <div class="modal-footer">
                    <form method="post">
                            <button class="btn btn-primary" type="submit" name="refresh">Refresh</span></button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="battleModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Battle news</h4>
                </div>
                <div class="modal-body">
                    <p>You are currently not in a battle</p>
                </div>
                <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div id="infoModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Server Statistics - Page Content</h4>
                </div>
                <div class="modal-body">
                    <p>This page contains a overall summary about the user statistics and data on your server.</p>
                    <p>You can see statistics which contain information of all time usage then monthly, weekly and daily usage.</p>
                    <p>This page receives its values out of a database. So the values might be delayed a bit.</p>
                </div>
                <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="wrapper">
        
        <!-- Navigation -->
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <a class="navbar-brand" href="index.php">Ranksystem - Statistics</a>
            </div>
            <!-- Top Menu Items -->
            <ul class="nav navbar-right top-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i><?PHP echo ' '.$_SESSION['tsname']; ?><b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="my_stats.php"><i class="fa fa-fw fa-user"></i> My Statistics</a>
                        </li>
                        <li>
                            <a href="#myModal" data-toggle="modal"><i class="fa fa-fw fa-envelope"></i> Server news</a>
                        </li>
                        <li>
                            <a href="#battleModal" data-toggle="modal"><span class="glyphicon glyphicon-fire" aria-hidden="true"></span> Battle news</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <div class="navbar-form navbar-center">
                        <div class="btn-group">
                            <a href="#myModal2" data-toggle="modal" class="btn btn-primary">
                                <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <li class="active">
                        <a href="index.php"><i class="fa fa-fw fa-area-chart"></i> Server Statistics</a>
                    </li>
                    <li>
                        <a href="my_stats.php"><i class="fa fa-fw fa-bar-chart-o"></i> My Statistics</a>
                    </li>
                    <li>
                        <a href="javascript:;" data-toggle="collapse" data-target="#demo"><i class="fa fa-fw fa-trophy"></i> Top Users <i class="fa fa-fw fa-caret-down"></i></a>
                        <ul id="demo" class="collapse">
                            <li>
                                <a href="top_week.php">Of The Week</a>
                            </li>
                            <li>
                                <a href="top_month.php">Of The Month</a>
                            </li>
                            <li>
                                <a href="top_all.php">Of All Time</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="battle_area.php"><span class="glyphicon glyphicon-fire" aria-hidden="true"></span> Battle Area</a>
                    </li>
                    <li>
                        <a href="info.php"><i class="fa fa-fw fa-info-circle"></i> Ranksystem Info</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </nav>

        <div id="page-wrapper">

            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            Server Statistics
                        <div class="btn-group">
                            <a href="#infoModal" data-toggle="modal" class="btn btn-primary">
                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                            </a>
                        </div>
                        <div class="pull-right"><small><font color="#000000">IP: </font><a href="ts3server://<?PHP echo ($ts['host']=='localhost' ? $_SERVER['HTTP_HOST'] : $ts['host']).':'.$ts['voice']; ?>"><?PHP echo ($ts['host']=='localhost' ? $_SERVER['HTTP_HOST'] : $ts['host']).':'.$ts['voice']; ?></a></small></div>
                        </h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-users fa-5x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?PHP echo $sql_res[0]['total_user'] ?></div>
                                        <div>Total Users</div>
                                    </div>
                                </div>
                            </div>
                            <a href="list_rankup.php" target="_blank">
                                <div class="panel-footer">
                                    <span class="pull-left">View Details</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-green">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-clock-o fa-5x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?PHP echo round(($sql_res[0]['total_online_time'] / 86400)). ' <small>days</small>';?></div>
                                        <div>Online Time / Total</div>
                                    </div>
                                </div>
                            </div>
                            <a href="top_all.php">
                                <div class="panel-footer">
                                    <span class="pull-left">View Top Of All Time</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-yellow">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-clock-o fa-5x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?PHP echo round(($sql_res[0]['total_online_month'] / 86400)). ' <small>days</small>';?></div>
										<div><?PHP echo ($sql_res[0]['total_online_month'] == 0 ? 'not enough data yet...' : 'Online Time / Month') ?></div>
                                    </div>
                                </div>
                            </div>
                            <a href="top_month.php">
                                <div class="panel-footer">
                                    <span class="pull-left">View Top Of The Month</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa fa-clock-o fa-5x"></i>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><?PHP echo round(($sql_res[0]['total_online_week'] / 86400)). ' <small>days</small>';?></div>
										<div><?PHP echo ($sql_res[0]['total_online_week'] == 0 ? 'not enough data yet...' : 'Online Time / Week') ?></div>
                                    </div>
                                </div>
                            </div>
                            <a href="top_week.php">
                                <div class="panel-footer">
                                    <span class="pull-left">View Top Of The Week</span>
                                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                    <div class="clearfix"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
								<div class="row">
									<div class="col-xs-9">
										<h3 class="panel-title"><i class="fa fa-bar-chart-o"></i> Server Usage <i><?PHP if($usage == 'week') { echo 'In The Last 7 Days'; } elseif ($usage == 'month') { echo 'In The Last 30 Days'; } else { echo 'In The Last 24 Hours'; } ?></i></h3>
									</div>
									<div class="col-xs-3">
										<div class="btn-group dropup pull-right">
										  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											select period <span class="caret"></span>
										  </button>
										  <ul class="dropdown-menu">
											<li><a href="<?PHP echo "?usage=day"; ?>">Day</a></li>
											<li><a href="<?PHP echo "?usage=week"; ?>">Week</a></li>
											<li><a href="<?PHP echo "?usage=month"; ?>">Month</a></li>
										  </ul>
										</div>
									</div>
								</div>
                            </div>
                            <div class="panel-body">
                                <div id="server-usage-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-lg-3">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> Active / Inactive Time</h3>
                            </div>
                            <div class="panel-body">
                                <div id="time-gap-donut"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="panel panel-green">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> Current Server Usage</h3>
                            </div>
                            <div class="panel-body">
                                <div id="server-usage-donut"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="panel panel-yellow">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> User Nationality</h3>
                            </div>
                            <div class="panel-body">
                                <div id="user-descent-donut"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> User Platforms</h3>
                            </div>
                            <div class="panel-body">
                                <div id="user-platform-donut"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-6">
                        <h2>Current Statistics</h2>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Requested Information</th>
                                        <th>Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Server Status</td>
                                        <td><?PHP echo ($sql_res[0]['server_status'] == 1 || $sql_res[0]['server_status'] == 3) ? '<font color="#00FF00">Online</font>' : '<font color="#FF0000">Offline</font>' ?></td>
                                    </tr>
                                    <tr>
                                        <td>Clients Online</td>
                                        <td><?PHP echo $sql_res[0]['server_used_slots'].' / ' .($sql_res[0]['server_used_slots'] + $sql_res[0]['server_free_slots']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Amount Of Channels</td>
                                        <td><?PHP echo $sql_res[0]['server_channel_amount'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Server Ping (in ms)</td>
                                        <td><?PHP echo $sql_res[0]['server_ping'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Total Bytes Downloaded (in MiB)</td>
                                        <td><?PHP echo round($sql_res[0]['server_bytes_down']/1048576) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Total Bytes Uploaded (in MiB)</td>
                                        <td><?PHP echo round($sql_res[0]['server_bytes_up']/1048576) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Server Uptime</td>
                                        <td><text id="days">00</text> Days, <text id="hours">00</text> Hours, <text id="minutes">00</text> Mins, <text id="seconds">00</text> Secs</td>
                                    </tr>
                                    <tr>
                                        <td>Packet Loss (in percent)</td>
                                        <td><?PHP echo $sql_res[0]['server_packet_loss'] * 100 ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h2>Overall Statistics</h2>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Requested Information</th>
                                        <th>Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Server Name</td>
                                        <td><?PHP echo $sql_res[0]['server_name'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Server IP + Port</td>
                                        <td><?PHP echo ($ts['host']=='localhost' ? $_SERVER['HTTP_HOST'] : $ts['host']) .':' .$ts3['virtualserver_port'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Server Password</td>
                                        <td><?PHP echo ($sql_res[0]['server_pass'] == '0') ? 'No (Server is Public)' : 'Yes (Server Is Private)' ?></td>
                                    </tr>
                                    <tr>
                                        <td>Server ID</td>
                                        <td><?PHP echo $sql_res[0]['server_id'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Server Platform</td>
                                        <td><?PHP echo $sql_res[0]['server_platform'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Server Version</td>
                                        <td><?PHP $ver_leng = strpos($sql_res[0]['server_version'], ' ');
                                            echo substr($sql_res[0]['server_version'], 0, $ver_leng) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Server Creation Date (dd/mm/yyyy)</td>
                                        <td><?PHP echo date('d/m/Y', $sql_res[0]['server_creation_date']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Report To Server List</td>
                                        <td><?PHP echo ($sql_res[0]['server_weblist'] == 1) ? '<a href="https://www.planetteamspeak.com/serverlist/result/server/ip/' .($ts['host']=='localhost' ? $_SERVER['HTTP_HOST'] : $ts['host']).':'.$ts['voice'] .'" target="_blank">Activated</a>' : 'Not Activated' ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <!-- /.container-fluid -->

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
    <!-- Scripts -->
    <script>
        Morris.Donut({
          element: 'time-gap-donut',
          data: [
            {label: "Active Time (in Days)", value: <?PHP echo round(($sql_res[0]['total_active_time'] / 86400)); ?>},
            {label: "Inactive Time (in Days)", value: <?PHP echo round(($sql_res[0]['total_inactive_time'] / 86400)); ?>},
          ]
        });
        Morris.Donut({
            element: 'server-usage-donut',
            data: [
                {label: "Used Slots", value: <?PHP echo $sql_res[0]['server_used_slots'] ?>},
                {label: "Free Slots", value: <?PHP echo $sql_res[0]['server_free_slots'] ?>},
            ],
            colors: [
                '#5cb85c',
                '#80ce80'
          ]
        });
        Morris.Donut({
          element: 'user-descent-donut',
          data: [
               {label: "<?PHP echo $sql_res[0]['country_nation_name_1'] ?>", value: <?PHP echo $sql_res[0]['country_nation_1'] ?>},
               {label: "<?PHP echo $sql_res[0]['country_nation_name_2'] ?>", value: <?PHP echo $sql_res[0]['country_nation_2'] ?>},
               {label: "<?PHP echo $sql_res[0]['country_nation_name_3'] ?>", value: <?PHP echo $sql_res[0]['country_nation_3'] ?>},
               {label: "<?PHP echo $sql_res[0]['country_nation_name_4'] ?>", value: <?PHP echo $sql_res[0]['country_nation_4'] ?>},
               {label: "<?PHP echo $sql_res[0]['country_nation_name_5'] ?>", value: <?PHP echo $sql_res[0]['country_nation_5'] ?>},
               {label: "Others", value: <?PHP echo $sql_res[0]['country_nation_other'] ?>},
          ],
            colors: [
                '#f0ad4e',
                '#ffc675',
                '#fecf8d',
                '#ffdfb1',
                '#fce8cb',
                '#fdf3e5'
          ]
        });
        Morris.Donut({
            element: 'user-platform-donut',
            data: [
                {label: "Windows", value: <?PHP echo $sql_res[0]['platform_1'] ?>},
                {label: "Linux", value: <?PHP echo $sql_res[0]['platform_3'] ?>},
                {label: "Android", value: <?PHP echo $sql_res[0]['platform_4'] ?>},
                {label: "iOS", value: <?PHP echo $sql_res[0]['platform_2'] ?>},
                {label: "OS X", value: <?PHP echo $sql_res[0]['platform_5'] ?>},
                {label: "Others", value: <?PHP echo $sql_res[0]['platform_other'] ?>},
            ],
            colors: [
                '#d9534f',
                '#FF4040',
                '#FF5050',
                '#FF6060',
                '#FF7070',
                '#FF8080'
          ]
        });
		Morris.Area({
		  element: 'server-usage-chart',
		  data: [
			<?PHP
				$chart_data = '';
				$trash_string = $mysqlcon->query("SET @a:=0");
				if($usage == 'week') { 
					$server_usage = $mysqlcon->query("SELECT u1.timestamp, u1.clients FROM (SELECT @a:=@a+1,mod(@a,12) AS test,timestamp,clients FROM $dbname.server_usage) AS u2, $dbname.server_usage AS u1 WHERE u1.timestamp=u2.timestamp AND u2.test='1' order by u2.timestamp DESC LIMIT 28");
				} elseif ($usage == 'month') {
					$server_usage = $mysqlcon->query("SELECT u1.timestamp, u1.clients FROM (SELECT @a:=@a+1,mod(@a,48) AS test,timestamp,clients FROM $dbname.server_usage) AS u2, $dbname.server_usage AS u1 WHERE u1.timestamp=u2.timestamp AND u2.test='1' order by u2.timestamp DESC LIMIT 30");
				} else {
					$server_usage = $mysqlcon->query("SELECT u1.timestamp, u1.clients FROM (SELECT @a:=@a+1,mod(@a,2) AS test,timestamp,clients FROM $dbname.server_usage) AS u2, $dbname.server_usage AS u1 WHERE u1.timestamp=u2.timestamp AND u2.test='1' order by u2.timestamp DESC LIMIT 24");
				}
				$server_usage = $server_usage->fetchAll(PDO::FETCH_ASSOC);
				foreach($server_usage as $chart_value) {
					$chart_time = date('Y-m-d H:i:s',$chart_value['timestamp']);
					$chart_data = $chart_data . '{ y: \''.$chart_time.'\', a: '.$chart_value['clients'].' }, ';
				}
				$chart_data = substr($chart_data, 0, -2);
				echo $chart_data;
			?>
		  ],
		  xkey: 'y',
		  ykeys: ['a'],
		  labels: ['Clients', 'Date']
		});
    </script>
    <script type="text/javascript">
        var daysLabel = document.getElementById("days");
        var hoursLabel = document.getElementById("hours");
        var minutesLabel = document.getElementById("minutes");
        var secondsLabel = document.getElementById("seconds");
        var totalSeconds = <?PHP echo $sql_res[0]['server_uptime'] ?>;
        setInterval(setTime, 1000);

        function setTime()
        {
            ++totalSeconds;
            secondsLabel.innerHTML = pad(totalSeconds%60);
            minutesLabel.innerHTML = pad(parseInt(totalSeconds/60)%60);
            hoursLabel.innerHTML = pad(parseInt(totalSeconds/3600)%24)
            daysLabel.innerHTML = pad(parseInt(totalSeconds/86400))
        }

        function pad(val)
        {
            var valString = val + "";
            if(valString.length < 2)
            {
                return "0" + valString;
            }
            else
            {
                return valString;
            }
        }
    </script>
</body>

</html>