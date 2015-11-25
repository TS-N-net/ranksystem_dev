<?PHP
session_start();
$starttime = microtime(true);

require_once('../other/config.php');
require_once('../ts3_lib/TeamSpeak3.php');
require_once('../lang.php');

if(isset($_POST['refresh'])) {
    $_SESSION = array();
    session_destroy();
}

try {
    $ts3 = TeamSpeak3::factory("serverquery://" . $ts['user'] . ":" . $ts['pass'] . "@" . $ts['host'] . ":" . $ts['query'] . "/?server_port=" . $ts['voice']);
	if (strlen($queryname)>27) $queryname = substr($queryname, 0, -3).'_st' else $queryname = $queryname .'_st';
	if (strlen($queryname2)>26) $queryname2 = substr($queryname2, 0, -4).'_st2' else $queryname2 = $queryname2.'_st2';
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
        if ($slowmode == 1)
        sleep(1);
        $allclients = $ts3->clientList();
        $hpip = ip2long($_SERVER['REMOTE_ADDR']);
        $matchip = 0;
         
        foreach ($allclients as $client) {
            $tsip                   = ip2long($client['connection_client_ip']);
            if ($hpip == $tsip) {
                $_SESSION['tsuid']          = htmlspecialchars($client['client_unique_identifier'], ENT_QUOTES);
                $_SESSION['tscldbid']       = $client['client_database_id'];
                $_SESSION['tsname']         = str_replace('\\', '\\\\', htmlspecialchars($client['client_nickname'], ENT_QUOTES));
                $_SESSION['tsavatar']       = $client['client_flag_avatar'];
                $_SESSION['tsavatarfile']   = $client->avatarDownload();
                $_SESSION['tscreated']      = date('d-m-Y',$client['client_created']);
                //$_SESSION['tsgroups']       = $client['client_servergroups'];
                $_SESSION['tsconnections']  = $client['client_totalconnections'];
                $avatarfilepath = '../other/avatars/'.$_SESSION['tsavatar'];
                file_put_contents($avatarfilepath, $_SESSION['tsavatarfile']);
                break;
            } else {
                $requestconnect = true;
                //wenn nicht auf ts oder ip adresse nicht mit homepage übereinstimmt
                //evtl. connect auf ts fordern oder abgespeckte seite anzeigen
            }
        }
    }
}

catch (Exception $e) {
    echo $lang['error'], $e->getCode(), ': ', $e->getMessage();
}

$dbdata = $mysqlcon->query("SELECT * FROM $dbname.user ORDER BY (count) DESC LIMIT 0, 10");

$db_arr = $dbdata->fetchAll();

$top10_sum = round(($db_arr[0]['count']/3600)) + round(($db_arr[1]['count']/3600)) + round(($db_arr[2]['count']/3600)) + round(($db_arr[3]['count']/3600)) + round(($db_arr[4]['count']/3600)) + round(($db_arr[5]['count']/3600)) + round(($db_arr[6]['count']/3600)) + round(($db_arr[7]['count']/3600)) + round(($db_arr[8]['count']/3600)) + round(($db_arr[9]['count']/3600));
$top10_idle_sum = round(($db_arr[0]['idle']/3600)) + round(($db_arr[1]['idle']/3600)) + round(($db_arr[2]['idle']/3600)) + round(($db_arr[3]['idle']/3600)) + round(($db_arr[4]['idle']/3600)) + round(($db_arr[5]['idle']/3600)) + round(($db_arr[6]['idle']/3600)) + round(($db_arr[7]['idle']/3600)) + round(($db_arr[8]['idle']/3600)) + round(($db_arr[9]['idle']/3600));

$all_sum_data = $mysqlcon->query("SELECT SUM(count) FROM $dbname.user");
$all_sum_data_res = $all_sum_data->fetchAll();
$others_sum = round(($all_sum_data_res[0][0]/3600)) - $top10_sum;

$all_idle_sum_data = $mysqlcon->query("SELECT SUM(idle) FROM $dbname.user");
$all_idle_sum_data_res = $all_idle_sum_data->fetchAll();
$others_idle_sum = round(($all_idle_sum_data_res[0][0]/3600)) - $top10_idle_sum;

$dbdata_full = $mysqlcon->query("SELECT * FROM $dbname.user");
$sumentries = $dbdata_full->rowCount() - 10;

function get_percentage($max_value, $value) {
    return (round(($value/$max_value)*100));
}
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
    <div id="myModal3" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Top 10 Of All Time - Page Content</h4>
                </div>
                <div class="modal-body">
                    <p>This page contains an overview about the top 10 users of all time.</p>
                    <p>You can also see the comparison statistics of the top 10 users vs the rest of the users on the server.</p>
                    <br>
                    <p><strong>TIP!</strong> If the name of the user is not fully displayed on your screen, just hover your mouse over the name to show the full length name of the user.</p>
                </div>
                <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
                    <li>
                        <a href="index.php"><i class="fa fa-fw fa-area-chart"></i> Server Statistics</a>
                    </li>
                    <li>
                        <a href="my_stats.php"><i class="fa fa-fw fa-bar-chart-o"></i> My Statistics</a>
                    </li>
                    <li class="active">
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
                            Top Users
                            <small>Of All Time</small>
                            <div class="btn-group">
                                <a href="#myModal3" data-toggle="modal" class="btn btn-primary">
                                    <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                                </a>
                            </div>
                        </h1>
                    </div>
                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-4 col-lg-offset-4">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <center><i>#1st</i></center>
                                        <center><i class="fa fa-trophy fa-5x"></i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[0]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[0]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[0]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-lg-offset-2">
                        <div class="panel panel-green">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <center><i>#2nd</i></center>
                                        <center><i class="fa fa-trophy fa-5x"></i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[1]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[1]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[1]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel panel-yellow">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <center><i>#3rd</i></center>
                                        <center><i class="fa fa-trophy fa-5x"></i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[2]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[2]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[2]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <center><i class="fa-3x">#4th</i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[3]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[3]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[3]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <center><i class="fa-3x">#5th</i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[4]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[4]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[4]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <center><i class="fa-3x">#6th</i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[5]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[5]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[5]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <div style="line-height:90%;">
                                            <br>
                                        </div>
                                        <center><i class="fa-2x">#7th</i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[6]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[6]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[6]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <div style="line-height:90%;">
                                            <br>
                                        </div>
                                        <center><i class="fa-2x">#8th</i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[7]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[7]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[7]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <div style="line-height:90%;">
                                            <br>
                                        </div>
                                        <center><i class="fa-2x">#9th</i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[8]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[8]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[8]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <div style="line-height:90%;">
                                            <br>
                                        </div>
                                        <center><i class="fa-2x">#10th</i></center>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><span title=<?PHP echo '"' .$db_arr[9]['name'] .'"'?>><?PHP echo str_replace(' ', '', $db_arr[9]['name']) ?></span></div>
                                        <div>With <?PHP echo round(($db_arr[9]['count']/3600))?> Hours Online Time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Top 10 Compared</h2>
                        <h4>#1 <?PHP echo $db_arr[0]['name'] ?><?PHP echo ($db_arr[0]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped <?PHP echo ($db_arr[0]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: 100%;"><?PHP echo '<font color="#000000">' .round(($db_arr[0]['count']/3600)) .' Hours (Defines 100 %)</font>'?>
                            </div>
                        </div>
                        <h4>#2 <?PHP echo $db_arr[1]['name'] ?><?PHP echo ($db_arr[1]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($db_arr[1]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($db_arr[0]['count'], $db_arr[1]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($db_arr[0]['count'], $db_arr[1]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($db_arr[1]['count']/3600)) .' Hours (' .get_percentage($db_arr[0]['count'], $db_arr[1]['count']) .' % - Compared To #1)</font>'?>
                            </div>
                        </div>
                        <h4>#3 <?PHP echo $db_arr[2]['name'] ?><?PHP echo ($db_arr[2]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($db_arr[2]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($db_arr[0]['count'], $db_arr[2]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($db_arr[0]['count'], $db_arr[2]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($db_arr[2]['count']/3600)) .' Hours (' .get_percentage($db_arr[0]['count'], $db_arr[2]['count']) .' % - Compared To #1)</font>'?>
                            </div>
                        </div>
                        <h4>#4 <?PHP echo $db_arr[3]['name'] ?><?PHP echo ($db_arr[3]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($db_arr[3]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($db_arr[0]['count'], $db_arr[3]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($db_arr[0]['count'], $db_arr[3]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($db_arr[3]['count']/3600)) .' Hours (' .get_percentage($db_arr[0]['count'], $db_arr[3]['count']) .' % - Compared To #1)</font>'?>
                            </div>
                        </div>
                        <h4>#5 <?PHP echo $db_arr[4]['name'] ?><?PHP echo ($db_arr[4]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped <?PHP echo ($db_arr[4]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($db_arr[0]['count'], $db_arr[4]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($db_arr[0]['count'], $db_arr[4]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($db_arr[4]['count']/3600)) .' Hours (' .get_percentage($db_arr[0]['count'], $db_arr[4]['count']) .' % - Compared To #1)</font>'?>
                            </div>
                        </div>
                        <h4>#6 <?PHP echo $db_arr[5]['name'] ?><?PHP echo ($db_arr[5]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($db_arr[5]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($db_arr[0]['count'], $db_arr[5]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($db_arr[0]['count'], $db_arr[5]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($db_arr[5]['count']/3600)) .' Hours (' .get_percentage($db_arr[0]['count'], $db_arr[5]['count']) .' % - Compared To #1)</font>'?>
                            </div>
                        </div>
                        <h4>#7 <?PHP echo $db_arr[6]['name'] ?><?PHP echo ($db_arr[6]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($db_arr[6]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($db_arr[0]['count'], $db_arr[6]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($db_arr[0]['count'], $db_arr[6]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($db_arr[6]['count']/3600)) .' Hours (' .get_percentage($db_arr[0]['count'], $db_arr[6]['count']) .' % - compared to #1)</font>'?>
                            </div>
                        </div>
                        <h4>#8 <?PHP echo $db_arr[7]['name'] ?><?PHP echo ($db_arr[7]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($db_arr[7]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($db_arr[0]['count'], $db_arr[7]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($db_arr[0]['count'], $db_arr[7]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($db_arr[7]['count']/3600)) .' Hours (' .get_percentage($db_arr[0]['count'], $db_arr[7]['count']) .' % - Compared To #1)</font>'?>
                            </div>
                        </div>
                        <h4>#9 <?PHP echo $db_arr[8]['name'] ?><?PHP echo ($db_arr[8]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped <?PHP echo ($db_arr[8]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($db_arr[0]['count'], $db_arr[8]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($db_arr[0]['count'], $db_arr[8]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($db_arr[8]['count']/3600)) .' Hours (' .get_percentage($db_arr[0]['count'], $db_arr[8]['count']) .' % - Compared To #1)</font>'?>
                            </div>
                        </div>
                        <h4>#10 <?PHP echo $db_arr[9]['name'] ?><?PHP echo ($db_arr[9]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
                        <div class="progress">
                            <div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($db_arr[9]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($db_arr[0]['count'], $db_arr[9]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($db_arr[0]['count'], $db_arr[9]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($db_arr[9]['count']/3600)) .' Hours (' .get_percentage($db_arr[0]['count'], $db_arr[9]['count']) .' % - Compared To #1)</font>'?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <h2>Top 10 Statistics</h2>
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> Top 10 Vs Others In Online Time</h3>
                            </div>
                            <div class="panel-body">
                                <div id="top10vs_donut1"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <h2><br></h2>
                        <div class="panel panel-green">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> Top 10 Vs Others In Active Time</h3>
                            </div>
                            <div class="panel-body">
                                <div id="top10vs_donut2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <h2><br></h2>
                        <div class="panel panel-yellow">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> Top 10 Vs Others In Inactive Time</h3>
                            </div>
                            <div class="panel-body">
                                <div id="top10vs_donut3"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <h2><br></h2>
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> Battles Won / Lost Of Top 10</h3>
                            </div>
                            <div class="panel-body">
                                <div id="top10vs_donut4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->
    <!-- /Scripts -->
    <?PHP

    ?>
    <script>
        Morris.Donut({
          element: 'top10vs_donut1',
          data: [
            {label: "Top 10 (in Hours)", value: <?PHP echo $top10_sum ?>},
            {label: "Other <?PHP echo $sumentries ?> Users (in Hours)", value: <?PHP echo $others_sum ?>},
          ]
        });
        Morris.Donut({
          element: 'top10vs_donut2',
          data: [
            {label: "Top 10 (in Hours)", value: <?PHP echo $top10_sum - $top10_idle_sum ?>},
            {label: "Other <?PHP echo $sumentries ?> Users (in Hours)", value: <?PHP echo $others_sum - $others_idle_sum ?>},
          ],
            colors: [
            '#5cb85c',
            '#80ce80'
        ]
        });
        Morris.Donut({
          element: 'top10vs_donut3',
          data: [
            {label: "Top 10 (in Hours)", value: <?PHP echo $top10_idle_sum ?>},
            {label: "Other <?PHP echo $sumentries ?> Users (in Hours)", value: <?PHP echo $others_idle_sum ?>},
          ],
          colors: [
            '#f0ad4e',
            '#ffc675'
        ]
        });
        Morris.Donut({
          element: 'top10vs_donut4',
          data: [
            {label: "Battles Won", value: 1337},
            {label: "Battles Lost", value: 666},
          ],
          colors: [
            '#d9534f',
            '#FF7070'
        ]
        });
    </script>
</body>

</html>