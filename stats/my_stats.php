<?PHP
session_start();
require_once('../other/config.php');
require_once('../ts3_lib/TeamSpeak3.php');
require_once('../lang.php');
require_once('../other/session.php');

if(!isset($_SESSION['tsuid'])) {
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
	}
}

$getstring = $_SESSION['tsuid'];
$searchmysql = 'WHERE uuid LIKE \'%'.$getstring.'%\'';

$dbdata = $mysqlcon->query("SELECT * FROM $dbname.user $searchmysql");
$dbdata_fetched = $dbdata->fetchAll();

$stats_user = $mysqlcon->query("SELECT * FROM $dbname.stats_user WHERE uuid='$getstring'");
$stats_user = $stats_user->fetchAll();

$count_week = $stats_user[0]['count_week'];
$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_week"); $count_week = $dtF->diff($dtT)->format($timeformat);
$count_month = $stats_user[0]['count_month'];
$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_month"); $count_month = $dtF->diff($dtT)->format($timeformat);

$time_for_bronze = 50;
$time_for_silver = 100;
$time_for_gold = 250;
$time_for_legendary = 500;

$connects_for_bronze = 10;
$connects_for_silver = 50;
$connects_for_gold = 100;
$connects_for_legendary = 250;

$battles_for_bronze = 5;
$battles_for_silver = 10;
$battles_for_gold = 25;
$battles_for_legendary = 50;

$achievements_done = 0;

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
    <link href="../bootstrap/addons/legendaryIcons.css" rel="stylesheet">

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
                    <h4 class="modal-title">My Statistics - Page Content</h4>
                </div>
                <div class="modal-body">
                    <p>This page contains a overall summary of your personal statistics and activity on the server.</p>
                    <p>It also contains your progress in unlocking achievements and an overview of your battles.</p>
                    <br>
                    <p><h4>Achievements</h4></p>
                    <p>You can unlock achievements in different categories from bronze to legendary depending on your activity on the server.</p>
                    <p>There are three achievements available: Online Time, Total connections to the server and battles won.</p>
                    <p>For every achievement there is four levels.</p>
                    <br>
                    <p><h4>Criterias</h4></p>
                    <p><b>Online Time</b></p>
                    <p><img src="../icons/MyStats/Unranked_Time.png" width="35" height="35" alt=""> 0 - 49 Hours: Unranked</p>
                    <p><img src="../icons/MyStats/Bronze_Time.png" width="35" height="35" alt=""> 50 - 99 Hours: Bronze</p>
                    <p><img src="../icons/MyStats/Silver_Time.png" width="35" height="35" alt=""> 100 - 249 Hours: Silver</p>
                    <p><img src="../icons/MyStats/Gold_Time.png" width="35" height="35" alt=""> 250 - 499 Hours: Gold</p>
                    <p><div id="cf4a" class="shadow">
                            <img src="../icons/MyStats/Legendary_Time_Red.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Time_Orange.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Time_Yellow.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Time_Green.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Time_Blue.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Time_Purple.png" width="35" height="35"/>
                        </div> 500+ Hours: Legendary</p>
                    <p><b>Connections To Server</b></p>
                    <p><img src="../icons/MyStats/Unranked_Connects.png" width="42" height="35" alt=""> 0 - 9 Connections: Unranked</p>
                    <p><img src="../icons/MyStats/Bronze_Connects.png" width="42" height="35" alt=""> 10 - 49 Connections: Bronze</p>
                    <p><img src="../icons/MyStats/Silver_Connects.png" width="42" height="35" alt=""> 50 - 99 Connections: Silver</p>
                    <p><img src="../icons/MyStats/Gold_Connects.png" width="42" height="35" alt=""> 100 - 249 Connections: Gold</p>
                    <p><div id="cf4a" class="shadow">
                            <img src="../icons/MyStats/Legendary_Connects_Red.png" width="42" height="35"/>
                            <img src="../icons/MyStats/Legendary_Connects_Orange.png" width="42" height="35"/>
                            <img src="../icons/MyStats/Legendary_Connects_Yellow.png" width="42" height="35"/>
                            <img src="../icons/MyStats/Legendary_Connects_Green.png" width="42" height="35"/>
                            <img src="../icons/MyStats/Legendary_Connects_Blue.png" width="42" height="35"/>
                            <img src="../icons/MyStats/Legendary_Connects_Purple.png" width="42" height="35"/>
                        </div> 250+ Connections: Legendary</p>
                    <p><b>Battles Won</b></p>
                    <p><img src="../icons/MyStats/Unranked_Battle.png" width="35" height="35" alt=""> 0 - 4 Battles: Unranked</p>
                    <p><img src="../icons/MyStats/Bronze_Battle.png" width="35" height="35" alt=""> 5 - 9 Battles: Bronze</p>
                    <p><img src="../icons/MyStats/Silver_Battle.png" width="35" height="35" alt=""> 10 - 24 Battles: Silver</p>
                    <p><img src="../icons/MyStats/Gold_Battle.png" width="35" height="35" alt=""> 25 - 49 Battles: Gold</p>
                    <p><div id="cf4a" class="shadow">
                            <img src="../icons/MyStats/Legendary_Battle_Red.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Battle_Orange.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Battle_Yellow.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Battle_Green.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Battle_Blue.png" width="35" height="35"/>
                            <img src="../icons/MyStats/Legendary_Battle_Purple.png" width="35" height="35"/>
                        </div> 50+ Battles: Legendary</p>
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
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i><?PHP echo '&nbsp;&nbsp;'.$_SESSION['tsname']; ?>&nbsp;<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <?PHP echo ($_SESSION['connected'] == 0 ? ' ' : '<li>
                            <a href="my_stats.php"><i class="fa fa-fw fa-user"></i> My Statistics</a>
                        </li>'); ?>
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
                    <li class="active">
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
                    <div class="col-lg-6">
                        <h1 class="page-header">
                            My Statistics
                            <div class="btn-group">
                            <a href="#infoModal" data-toggle="modal" class="btn btn-primary">
                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                            </a>
                        </div>
                        </h1>
                    </div>
                    <div class="col-lg-6">
                        <h1 class="page-header">
                            <small><font color="#000000">My Achievements</font></small>
                        </h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-9 text-left">
                                        <div class="huge"><?PHP echo $_SESSION['tsname'] ?></div>
                                        <div>Rank #1337</div>
                                    </div>
                                    <div class="col-xs-3">
                                        <center>
                                        <?PHP
                                            if(isset($_SESSION['tsavatar']) && $_SESSION['tsavatar'] != "none") {
                                                echo '<img src="../other/avatars/'.$_SESSION['tsavatar'].'" class="img-rounded" alt="avatar" height="70" align="right">';
                                            } else {
                                                echo '<i class="fa fa-user fa-5x" align="right"></i>';
                                            }
                                        ?>
                                        </center>
                                    </div>
                                </div>
                            </div>
                                <div class="panel-footer">
                                    <span class="pull-left">
                                        <p><strong><font color="#337ab7">Database ID:</font></strong></p>
                                        <p><strong><font color="#5cb85c">Total Online Time:</font></strong></p>
                                        <p><strong><font color="#f0ad4e">Total Connects To The Server:</font></strong></p>
                                        <p><strong><font color="#d9534f">Online Time This Week:</font></strong></p>
                                        <p><strong><font color="#337ab7">Online Time This Month:</font></strong></p>
                                        <p><strong><font color="#5cb85c">Total Battles:</font></strong></p>
                                        <p><strong><font color="#f0ad4e">Achievements Completed:</font></strong></p>
                                        <p><strong><font color="#d9534f">First Connection To Server:</font></strong></p>
                                    </span>
                                    <span class="pull-right">
                                        <p align="right"><?PHP echo $dbdata_fetched[0]['cldbid']; ?></p>
                                        <p align="right"><text id="days">00</text> Days, <text id="hours">00</text> Hours, <text id="minutes">00</text> Mins, <text id="seconds">00</text> Secs</p>
                                        <p align="right"><?PHP echo $_SESSION['tsconnections']; ?></p>
                                        <p align="right"><?PHP echo $count_week; ?></p>
                                        <p align="right"><?PHP echo $count_month; ?></p>
                                        <p align="right">test4</p>
                                        <p align="right"><?PHP
                                                            if(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_legendary) {
                                                               $achievements_done = $achievements_done + 4; 
                                                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_gold) {
                                                                $achievements_done = $achievements_done + 3;
                                                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_silver) {
                                                                $achievements_done = $achievements_done + 2;
                                                            } else {
                                                                $achievements_done = $achievements_done + 1;
                                                            }
                                                            if($_SESSION['tsconnections'] >= $connects_for_legendary) {
                                                               $achievements_done = $achievements_done + 4;
                                                            } elseif($_SESSION['tsconnections'] >= $connects_for_gold) {
                                                                $achievements_done = $achievements_done + 3;
                                                            } elseif($_SESSION['tsconnections'] >= $connects_for_silver) {
                                                                $achievements_done = $achievements_done + 2;
                                                            } else {
                                                                $achievements_done = $achievements_done + 1;
                                                            }
                                                            echo $achievements_done .' / 12';
                                                            ?></p>
                                        <p align="right"><?PHP echo $_SESSION['tscreated']; ?></p>
                                    </span>
                                    <div class="clearfix"></div>
                                </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div style="line-height:50%;">
                            <br>
                        </div>
                        <div class="panel panel-green">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <?PHP
                                        if(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_legendary) {
                                            echo '<div id="cf4a" class="shadow">
                                                    <img src="../icons/MyStats/Legendary_Time_Red.png" />
                                                    <img src="../icons/MyStats/Legendary_Time_Orange.png" />
                                                    <img src="../icons/MyStats/Legendary_Time_Yellow.png" />
                                                    <img src="../icons/MyStats/Legendary_Time_Green.png" />
                                                    <img src="../icons/MyStats/Legendary_Time_Blue.png" />
                                                    <img src="../icons/MyStats/Legendary_Time_Purple.png" />
                                                </div>';
                                        } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_gold) {
                                            echo '<center><img src="../icons/MyStats/Gold_Time.png" width="74" height="74" alt=""></center>';
                                        } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_silver) {
                                            echo '<center><img src="../icons/MyStats/Silver_Time.png" width="74" height="74" alt=""></center>';
                                        } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_bronze) {
                                            echo '<center><img src="../icons/MyStats/Bronze_Time.png" width="74" height="74" alt=""></center>';
                                        } else {
                                            echo '<center><img src="../icons/MyStats/Unranked_Time.png" width="74" height="74" alt=""></center>';
                                        }
                                        ?>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><small><?PHP
                                                            if(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_legendary) {
                                                                echo 'Time: Legendary';
                                                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_gold) {
                                                                echo 'Time: Gold';
                                                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_silver) {
                                                                echo 'Time: Silver';
                                                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_bronze) {
                                                                echo 'Time: Bronze';
                                                            } else {
                                                                echo 'Time: Unranked';
                                                            }
                                                            ?></span></small></div>
                                        <div><?PHP echo 'Because You Have A Online Time Of ' .round(($dbdata_fetched[0]['count']/3600)) .' hours.'; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="panel panel-yellow">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <?PHP
                                        if($_SESSION['tsconnections'] >= $connects_for_legendary) {
                                            echo '<div id="cf4a" class="shadow">
                                                    <img src="../icons/MyStats/Legendary_Connects_Red.png" />
                                                    <img src="../icons/MyStats/Legendary_Connects_Orange.png" />
                                                    <img src="../icons/MyStats/Legendary_Connects_Yellow.png" />
                                                    <img src="../icons/MyStats/Legendary_Connects_Green.png" />
                                                    <img src="../icons/MyStats/Legendary_Connects_Blue.png" />
                                                    <img src="../icons/MyStats/Legendary_Connects_Purple.png" />
                                                </div>';
                                        } elseif($_SESSION['tsconnections'] >= $connects_for_gold) {
                                            echo '<center><img src="../icons/MyStats/Gold_Connects.png" width="85" height="74" alt=""></center>';
                                        } elseif($_SESSION['tsconnections'] >= $connects_for_silver) {
                                            echo '<center><img src="../icons/MyStats/Silver_Connects.png" width="85" height="74" alt=""></center>';
                                        } elseif($_SESSION['tsconnections'] >= $connects_for_bronze) {
                                            echo '<center><img src="../icons/MyStats/Bronze_Connects.png" width="85" height="74" alt=""></center>';
                                        } else {
                                            echo '<center><img src="../icons/MyStats/Unranked_Connects.png" width="85" height="74" alt=""></center>';
                                        }
                                        ?>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><small><?PHP 
                                                            if($_SESSION['tsconnections'] >= $connects_for_legendary) {
                                                                echo 'Connects: Legendary';
                                                            } elseif($_SESSION['tsconnections'] >= $connects_for_gold) {
                                                                echo 'Connects: Gold';
                                                            } elseif($_SESSION['tsconnections'] >= $connects_for_silver) {
                                                                echo 'Connects: Silver';
                                                            } elseif($_SESSION['tsconnections'] >= $connects_for_bronze) {
                                                                echo 'Connects: Bronze';
                                                            } else {
                                                                echo 'Connects: Unranked';
                                                            }
                                                        ?></span></small></div>
                                        <div><?PHP echo 'Because You Connected ' .$_SESSION['tsconnections'] .' Times To The Server.'; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 pull-bottom">
                        <div class="panel panel-red">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <div id="cf4a" class="shadow">
                                            <img src="../icons/MyStats/Legendary_Battle_Red.png" />
                                            <img src="../icons/MyStats/Legendary_Battle_Orange.png" />
                                            <img src="../icons/MyStats/Legendary_Battle_Yellow.png" />
                                            <img src="../icons/MyStats/Legendary_Battle_Green.png" />
                                            <img src="../icons/MyStats/Legendary_Battle_Blue.png" />
                                            <img src="../icons/MyStats/Legendary_Battle_Purple.png" />
                                        </div>
                                    </div>
                                    <div class="col-xs-9 text-right">
                                        <div class="huge"><small>Battles: Unranked</span></small></div>
                                        <div>Because You Have Won xxx Battles.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> Battles Won / Lost</h3>
                            </div>
                            <div class="panel-body">
                                <div id="battles-donut"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h3>Time Achievement Progress</h3>
                        <div class="progress">
                            <div class="progress-bar progress-bar-success progress-bar-striped <?PHP
                            if(round(($dbdata_fetched[0]['count']/3600)) < $time_for_legendary) {
                                echo 'active';
                            }
                            ?>" role="progressbar" aria-valuenow="<?PHP
                            if(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_legendary) {
                                echo '100';
                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_gold) {
                                echo get_percentage($time_for_legendary, round($dbdata_fetched[0]['count']/3600));
                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_silver) {
                                echo get_percentage($time_for_gold, round($dbdata_fetched[0]['count']/3600));
                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_bronze) {
                                echo get_percentage($time_for_silver, round($dbdata_fetched[0]['count']/3600));
                            } else {
                                echo get_percentage($time_for_bronze, round($dbdata_fetched[0]['count']/3600));
                            }
                            ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?PHP
                            if(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_legendary) {
                                echo '100';
                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_gold) {
                                echo get_percentage($time_for_legendary, round($dbdata_fetched[0]['count']/3600));
                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_silver) {
                                echo get_percentage($time_for_gold, round($dbdata_fetched[0]['count']/3600));
                            } elseif(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_bronze) {
                                echo get_percentage($time_for_silver, round($dbdata_fetched[0]['count']/3600));
                            } else {
                                echo get_percentage($time_for_bronze, round($dbdata_fetched[0]['count']/3600));
                            }
                            ?>%;"><font color="#000000"><?PHP
                            if(round(($dbdata_fetched[0]['count']/3600)) >= $time_for_legendary) {
                                echo 'Progress Completed';
                            } elseif(round(($dbdata_fetched[0]['count']/3600)) < $time_for_legendary && round(($dbdata_fetched[0]['count']/3600)) >= $time_for_gold) {
                                echo get_percentage($time_for_legendary, round($dbdata_fetched[0]['count']/3600)) .'% Completed For Legendary';
                            } elseif(round(($dbdata_fetched[0]['count']/3600)) < $time_for_gold && round(($dbdata_fetched[0]['count']/3600)) >= $time_for_silver) {
                                echo get_percentage($time_for_gold, round($dbdata_fetched[0]['count']/3600)) .'% Completed For Gold';
                            } elseif(round(($dbdata_fetched[0]['count']/3600)) < $time_for_silver && round(($dbdata_fetched[0]['count']/3600)) >= $time_for_bronze) {
                                echo get_percentage($time_for_silver, round($dbdata_fetched[0]['count']/3600)) .'% Completed For Silver';
                            } else {
                                echo get_percentage($time_for_bronze, round($dbdata_fetched[0]['count']/3600)) .'% Completed For Bronze';
                            }
                            ?></font></div>
                        </div>
                        <h3>Connection Achievement Progress</h3>
                        <div class="progress">
                            <div class="progress-bar progress-bar-warning progress-bar-striped <?PHP
                            if ($_SESSION['tsconnections'] < $connects_for_legendary) {
                                echo 'active';
                            }
                            ?>" role="progressbar" aria-valuenow="<?PHP
                            if ($_SESSION['tsconnections'] >= $connects_for_legendary) {
                                echo '100';
                            } elseif($_SESSION['tsconnections'] >= $connects_for_gold) {
                                echo get_percentage($connects_for_legendary, $_SESSION['tsconnections']);
                            } elseif($_SESSION['tsconnections'] >= $connects_for_silver) {
                                echo get_percentage($connects_for_gold, $_SESSION['tsconnections']);
                            } elseif($_SESSION['tsconnections'] >= $connects_for_bronze) {
                                echo get_percentage($connects_for_silver, $_SESSION['tsconnections']);
                            } else {
                                echo get_percentage($connects_for_bronze, $_SESSION['tsconnections']);
                            }
                            ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?PHP
                            if ($_SESSION['tsconnections'] >= $connects_for_legendary) {
                                echo '100';
                            } elseif($_SESSION['tsconnections'] >= $connects_for_gold) {
                                echo get_percentage($connects_for_legendary, $_SESSION['tsconnections']);
                            } elseif($_SESSION['tsconnections'] >= $connects_for_silver) {
                                echo get_percentage($connects_for_gold, $_SESSION['tsconnections']);
                            } elseif($_SESSION['tsconnections'] >= $connects_for_bronze) {
                                echo get_percentage($connects_for_silver, $_SESSION['tsconnections']);
                            } else {
                                echo get_percentage($connects_for_bronze, $_SESSION['tsconnections']);
                            }
                            ?>%;"><font color="#000000"><?PHP
                            if($_SESSION['tsconnections'] >= $connects_for_legendary) {
                                echo 'Progress Completed';
                            } elseif($_SESSION['tsconnections'] < $connects_for_legendary && ($_SESSION['tsconnections'] >= $connects_for_gold)) {
                                echo get_percentage($connects_for_legendary, $_SESSION['tsconnections']) .'% Completed For Legendary';
                            } elseif($_SESSION['tsconnections'] < $connects_for_gold && ($_SESSION['tsconnections'] >= $connects_for_silver)) {
                                echo get_percentage($connects_for_gold, $_SESSION['tsconnections']) .'% Completed For Gold';
                            } elseif($_SESSION['tsconnections'] < $connects_for_silver && ($_SESSION['tsconnections'] >= $connects_for_bronze)) {
                                echo get_percentage($connects_for_silver, $_SESSION['tsconnections']) .'% Completed For Silver';
                            } else {
                                echo get_percentage($connects_for_bronze, $_SESSION['tsconnections']) .'% Completed For Bronze';
                            }
                            ?></font></div>
                        </div>
                        <h3>Battle Achievement Progress</h3>
                        <div class="progress">
                            <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"><span class="sr-only"></span></div>
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
    <script type="text/javascript">
        var daysLabel = document.getElementById("days");
        var hoursLabel = document.getElementById("hours");
        var minutesLabel = document.getElementById("minutes");
        var secondsLabel = document.getElementById("seconds");
        var totalSeconds = <?PHP echo $dbdata_fetched[0]['count'] ?>;
        setTime();
        setInterval(setTime, 1000);

        function setTime() {
            ++totalSeconds;
            secondsLabel.innerHTML = pad(totalSeconds%60);
            minutesLabel.innerHTML = pad(parseInt(totalSeconds/60)%60);
            hoursLabel.innerHTML = pad(parseInt(totalSeconds/3600)%24)
            daysLabel.innerHTML = pad(parseInt(totalSeconds/86400))
        }

        function pad(val) {
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
    <script>
    Morris.Donut({
          element: 'time-gap-donut',
          data: [
            {label: "Active Time (in Hours)", value: <?PHP echo round(($dbdata_fetched[0]['count'] - $dbdata_fetched[0]['idle'])/3600); ?>},
            {label: "Inactive Time (in Hours)", value: <?PHP echo round($dbdata_fetched[0]['idle']/3600); ?>},
          ]
        });
    Morris.Donut({
          element: 'battles-donut',
          data: [
            {label: "Battles Won", value: 1337},
            {label: "Battles Lost", value: 9999},
          ]
        });
    </script>
</body>

</html>