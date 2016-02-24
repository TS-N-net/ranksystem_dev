<?PHP
session_start();
require_once('../other/config.php');
require_once('../other/session.php');

if(!isset($_SESSION['tsuid'])) {
	$hpclientip = ip2long($_SERVER['REMOTE_ADDR']);
	set_session_ts3($hpclientip, $ts['voice'], $mysqlcon, $dbname);
}

$getstring = $_SESSION['tsuid'];
$searchmysql = 'WHERE uuid LIKE \'%'.$getstring.'%\'';

$dbdata = $mysqlcon->query("SELECT * FROM $dbname.user $searchmysql");
$dbdata_fetched = $dbdata->fetchAll();
$count_hours = round($dbdata_fetched[0]['count']/3600);

$stats_user = $mysqlcon->query("SELECT * FROM $dbname.stats_user WHERE uuid='$getstring'");
$stats_user = $stats_user->fetchAll();

if (isset($stats_user[0]['count_week'])) $count_week = $stats_user[0]['count_week']; else $count_week = 0;
$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_week"); $count_week = $dtF->diff($dtT)->format($timeformat);
if (isset($stats_user[0]['count_month'])) $count_month = $stats_user[0]['count_month']; else $count_month = 0;
$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_month"); $count_month = $dtF->diff($dtT)->format($timeformat);
if (isset($dbdata_fetched[0]['count'])) $count_total = $dbdata_fetched[0]['count']; else $count_total = 0;
$dtF = new DateTime("@0"); $dtT = new DateTime("@$count_total"); $count_total = $dtF->diff($dtT)->format($timeformat);

$time_for_bronze = 50;
$time_for_silver = 100;
$time_for_gold = 250;
$time_for_legendary = 500;

$connects_for_bronze = 10;
$connects_for_silver = 50;
$connects_for_gold = 100;
$connects_for_legendary = 250;

$achievements_done = 0;

if($count_hours >= $time_for_legendary) {
	$achievements_done = $achievements_done + 4; 
} elseif($count_hours >= $time_for_gold) {
	$achievements_done = $achievements_done + 3;
} elseif($count_hours >= $time_for_silver) {
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
										<div><?PHP echo 'Rank #' .$dbdata_fetched[0]['rank']; ?></div>
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
									<p><strong><font color="#f0ad4e">Unique ID:</font></strong></p>
									<p><strong><font color="#5cb85c">Total Connections To The Server:</font></strong></p>
									<p><strong><font color="#d9534f">Start Date For Statistics:</font></strong></p>
									<p><strong><font color="#337ab7">Total Online Time:</font></strong></p>
									<p><strong><font color="#f0ad4e">Online Time Last 7 Days:</font></strong></p>
									<p><strong><font color="#5cb85c">Online Time Last 30 Days:</font></strong></p>
									<p><strong><font color="#d9534f">Achievements Completed:</font></strong></p>
								</span>
								<span class="pull-right">
									<p align="right"><?PHP echo $dbdata_fetched[0]['cldbid']; ?></p>
									<p align="right"><?PHP echo $dbdata_fetched[0]['uuid']; ?></p>
									<p align="right"><?PHP echo $_SESSION['tsconnections']; ?></p>
									<p align="right"><?PHP echo $_SESSION['tscreated']; ?></p>
									<p align="right"><?PHP echo $count_total; ?></p>
									<p align="right"><?PHP echo $count_week; ?></p>
									<p align="right"><?PHP echo $count_month; ?></p>
									<p align="right"><?PHP echo $achievements_done .' / 8'; ?></p>
								</span>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<h3>Time Achievement Progress</h3>
						<?PHP if($count_hours >= $time_for_legendary) { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small>Time: Legendary</span></small>
										</div>
										<div><?PHP echo 'Because You Have A Online Time Of ' .$count_hours .' hours.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
								<font color="#000000">Progress Completed</font>
							</div>
						</div>
						<?PHP } elseif($count_hours >= $time_for_gold) { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small>Time: Gold</span></small>
										</div>
										<div><?PHP echo 'Because You Have A Online Time Of ' .$count_hours .' hours.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($time_for_gold, $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
								<font color="#000000">% Completed For Legendary</font>
							</div>
						</div>
						<?PHP } elseif($count_hours >= $time_for_silver) { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small>Time: Silver</span></small>
										</div>
										<div><?PHP echo 'Because You Have A Online Time Of ' .$count_hours .' hours.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($time_for_silver, $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
								<font color="#000000">% Completed For Gold</font>
							</div>
						</div>
						<?PHP } elseif($count_hours >= $time_for_bronze) { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small>Time: Bronze</span></small>
										</div>
										<div><?PHP echo 'Because You Have A Online Time Of ' .$count_hours .' hours.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="<?PHP echo get_percentage($time_for_bronze, $count_hours); ?>" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
								<font color="#000000">% Completed For Silver</font>
							</div>
						</div>
						<?PHP } else { ?>
						<div class="panel panel-green">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge">
											<small>Time: Unranked</span></small>
										</div>
										<div><?PHP echo 'Because You Have A Online Time Of ' .$count_hours .' hours.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
								<font color="#000000">% Completed For Bronze</font>
							</div>
						</div>
						<?PHP } ?>
					</div>
					<div class="col-lg-6">
						<h3>Connection Achievement Progress</h3>
						<?PHP if($_SESSION['tsconnections'] >= $connects_for_legendary) { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small>Connects: Legendary</span></small>
										</div>
										<div><?PHP echo 'Because You Connected ' .$_SESSION['tsconnections'] .' Times To The Server.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%;">
								<font color="#000000">Progress Completed</font>
							</div>
						</div>
						<?PHP } elseif($_SESSION['tsconnections'] >= $connects_for_gold) { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small>Connects: Gold</span></small>
										</div>
										<div><?PHP echo 'Because You Connected ' .$_SESSION['tsconnections'] .' Times To The Server.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active role="progressbar" aria-valuenow="<?PHP get_percentage($connects_for_gold, $_SESSION['tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="width:100%;">
								<font color="#000000"><?PHP echo get_percentage($connects_for_legendary, $_SESSION['tsconnections']); ?>% Completed For Legendary</font>
							</div>
						</div>
						<?PHP } elseif($_SESSION['tsconnections'] >= $connects_for_silver) { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small>Connects: Silver</span></small>
										</div>
										<div><?PHP echo 'Because You Connected ' .$_SESSION['tsconnections'] .' Times To The Server.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active role="progressbar" aria-valuenow="<?PHP get_percentage($connects_for_silver, $_SESSION['tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="width:100%;">
								<font color="#000000"><?PHP echo get_percentage($connects_for_gold, $_SESSION['tsconnections']); ?>% Completed For Gold</font>
							</div>
						</div>
						<?PHP } elseif($_SESSION['tsconnections'] >= $connects_for_bronze) { ?>				
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small>Connects: Bronze</span></small>
										</div>
										<div><?PHP echo 'Because You Connected ' .$_SESSION['tsconnections'] .' Times To The Server.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active role="progressbar" aria-valuenow="<?PHP get_percentage($connects_for_bronze, $_SESSION['tsconnections']); ?>" aria-valuemin="0" aria-valuemax="100" style="width:100%;">
								<font color="#000000"><?PHP echo get_percentage($connects_for_silver, $_SESSION['tsconnections']); ?>% Completed For Silver</font>
							</div>
						</div>
						<?PHP } else { ?>
						<div class="panel panel-yellow">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-right">
										<div class="huge"><small>Connects: Unranked</span></small>
										</div>
										<div><?PHP echo 'Because You Connected ' .$_SESSION['tsconnections'] .' Times To The Server.'; ?></div>
									</div>
								</div>
							</div>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped active role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:100%;">
								<font color="#000000"><?PHP echo get_percentage($connects_for_bronze, $_SESSION['tsconnections']); ?>% Completed For Bronze</font>
							</div>
						</div>
						<?PHP } ?>
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