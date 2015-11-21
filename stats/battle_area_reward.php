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
    if ($slowmode == 1)
        sleep(1);
    try {
        $ts3->selfUpdate(array(
            'client_nickname' => $queryname
        ));
    }
    catch (Exception $e) {
        if ($slowmode == 1)
            sleep(1);
        try {
            $ts3->selfUpdate(array(
                'client_nickname' => $queryname2
            ));
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
                $avatarfilepath = 'other/avatars/'.$_SESSION['tsavatar'];
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
                    <h4 class="modal-title">Battle Area - Page Content</h4>
                </div>
                <div class="modal-body">
                    <p>You can challenge other users in a battle between two users or two teams.</p>
                    <p>While the battle is active the online time of the teams/users will be counted.</p>
                    <p>When the battle ends the team/user with the highest online time wins.</p>
                    <p>(The regular battling time is 48 hours)</p>
                    <p>The winning team/user will recieve a price, which the user can use whenever the user wants.</p>
                    <p>It will be displayed on the <a href="my_stats.php">My Statistics</a> tab.</p>
                    <p>(Could be online time boost(2x) for 8 hours, instant online time (4 hours), etc.</p>
                    <p>These boosts can be used for example to climb in the top users of the week)</p>

                </div>
                <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div id="myModal4" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Sicarius Battle Info</h4>
                </div>
                <div class="modal-body">
                    <p>Onlinetime in Battle #1337: 04 hours 30 min</p>
                    <p>Battles Won: 1</p>
                    <p>Battles Lost: 1403</p>
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
                <a class="navbar-brand" href="stats_index.php">Ranksystem - Statistics</a>
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
                        <a href="stats_index.php"><i class="fa fa-fw fa-area-chart"></i> Server Statistics</a>
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
                    <li class="active">
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
                            Battle Area
                            <div class="btn-group">
                            <a href="#myModal3" data-toggle="modal" class="btn btn-primary">
                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                            </a>
                        </div>
                        </h1>
                    </div>
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <center><img src="../icons/BattleSite/Main_Grey.png" class="img-responsive"></center>
                                    <div class="clearfix"></div>
                                </div>
                                <a href="battle_area.php">
                                    <div class="panel-footer">
                                        <span class="pull-left">Main Site</span>
                                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="panel panel-green">
                                <div class="panel-heading">
                                    <center><img src="../icons/BattleSite/Top10_Grey.png" class="img-responsive"></center>
                                    <div class="clearfix"></div>
                                </div>
                                <a href="battle_area_top.php">
                                    <div class="panel-footer">
                                        <span class="pull-left">Top Battlers</span>
                                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="panel panel-yellow">
                                <div class="panel-heading">
                                    <center><img src="../icons/BattleSite/Reward_Colour.png" class="img-responsive"></center>
                                    <div class="clearfix"></div>
                                </div>
                                <a href="battle_area_reward.php">
                                    <div class="panel-footer">
                                        <span class="pull-left">Rewards</span>
                                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="panel panel-red">
                                <div class="panel-heading">
                                    <center><img src="../icons/BattleSite/Info_Grey.png" class="img-responsive"></center>
                                    <div class="clearfix"></div>
                                </div>
                                <a href="battle_area_info.php">
                                    <div class="panel-footer">
                                        <span class="pull-left">Detailed Battle System Description</span>
                                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                        <div class="clearfix"></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8 col-lg-offset-2">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <div class="panel-title">Battle Log</div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-3">
                                        <i class="fa-5x"><span class="glyphicon glyphicon-fire" area-hidden="true"></span></i>
                                    </div>
                                    <div class="panel-body">
                                        <p><strong><21.10.2015 - 17:00></strong> Battle between <strong><a href="#myModal4" data-toggle="modal">Sicarius</a></strong> and <strong><a href="#myModal5" data-toggle="modal">Maxi</a></strong> started! Battle Number <a>#1337</a></p>
                                    </div>
                                </div>
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
</body>

</html>