<?PHP
session_start();
require_once('../other/config.php');
require_once('../ts3_lib/TeamSpeak3.php');
require_once('../lang.php');
require_once('../other/session.php');

if(!isset($_SESSION['tsuid'])) {
	$hpclientip = ip2long($_SERVER['REMOTE_ADDR']);
	set_session_ts3($hpclientip, $ts['voice'], $mysqlcon, $dbname);
}

$getstring = $_SESSION['tsuid'];
$searchmysql = 'WHERE uuid LIKE \'%'.$getstring.'%\'';

$dbdata = $mysqlcon->query("SELECT * FROM $dbname.user $searchmysql");
$dbdata_fetched = $dbdata->fetchAll();

$stats_user = $mysqlcon->query("SELECT * FROM $dbname.stats_user WHERE uuid='$getstring'");
$stats_user = $stats_user->fetchAll();

if (isset($stats_user[0]['count_week'])) $count_week = $stats_user[0]['count_week']; else $count_week = 0;
$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_week"); $count_week = $dtF->diff($dtT)->format($timeformat);
if (isset($stats_user[0]['count_month'])) $count_month = $stats_user[0]['count_month']; else $count_month = 0;
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
require_once('nav.php');
?>
        <div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, 3); ?>
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
                                        <p><strong><font color="#f0ad4e">Total Connections To The Server:</font></strong></p>
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