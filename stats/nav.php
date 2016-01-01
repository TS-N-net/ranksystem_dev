<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="version" content="<?PHP echo $currvers; ?>">
	<link rel="icon" href="../icons/rs.png">

	<title>TS-N.NET Ranksystem</title>

	<!-- Bootstrap Core CSS -->
	<link href="../bootstrap/css/bootstrap.css" rel="stylesheet">

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
							<button class="btn btn-primary" type="submit" name="refresh">Refresh</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</form>
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
	<div id="myStatsModal" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title">Not available</h4>
				</div>
				<div class="modal-body">
					<p>You are not connected to the TS3 Server, so it cant display any data for you</p>
					<p>Please connect to the TS3 Server and then Refresh your Session by pressing the blue Refresh Button at the top-right corner</p>
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
					<p>The informations are collected since the beginning of the Ranksystem, they are not since the beginning of the TeamSpeak server.</p>
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
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<a class="navbar-brand" href="index.php">Ranksystem - Statistics</a>
			</div>
			<!-- Top Menu Items -->
			<?PHP if(basename($_SERVER['SCRIPT_NAME']) == "list_rankup.php") { ?>
			<ul class="nav navbar-left top-nav">
				<div class="navbar-form navbar-right dropdown">
					<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						Limit entries
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=50&amp;lang=$language&amp;search=$getstring"; ?>">50</a></li>
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=100&amp;lang=$language&amp;search=$getstring"; ?>">100</a></li>
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=250&amp;lang=$language&amp;search=$getstring"; ?>">250</a></li>
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=500&amp;lang=$language&amp;search=$getstring"; ?>">500</a></li>
						<li role="separator" class="divider"></li>
						<li role="presentation"><a role="menuitem" href="<?PHP echo "?sort=$keysort&amp;order=$keyorder&amp;user=all&amp;lang=$language&amp;search=$getstring"; ?>">all</a></li>
					</ul>
				</div>
				<div class="navbar-form navbar-right">
					<form method="post">
						<div class="form-group">
							<input class="form-control" type="text" name="usersuche" placeholder="Search"<?PHP if(isset($getstring)) echo 'value="'.$getstring.'"'; ?>>
						</div>
						<button class="btn btn-primary" type="submit" name="username"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
					</form>
				</div>
			</ul>
			<?PHP } ?>
			<ul class="nav navbar-right top-nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i><?PHP echo '&nbsp;&nbsp;' .($_SESSION['connected'] == 0 ? '(Not Connected To TS3!)' : $_SESSION['tsname']); ?>&nbsp;<b class="caret"></b></a>
					<ul class="dropdown-menu">
						<?PHP echo (!isset($_SESSION['tsname']) ? ' ' : '<li><a href="my_stats.php"><i class="fa fa-fw fa-user"></i> My Statistics</a></li>'); ?>
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
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "index.php" ? ' class="active">' : '>'); ?>
						<a href="index.php"><i class="fa fa-fw fa-area-chart"></i> Server Statistics</a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "my_stats.php" ? ' class="active">' : '>'); ?>
						<?PHP if($_SESSION['connected'] == 0) {
							echo '<a href="#myStatsModal" data-toggle="modal"><i class="fa fa-fw fa-exclamation-triangle"></i> *My Statistics</a>';
						} else {
							echo '<a href="my_stats.php"><i class="fa fa-fw fa-bar-chart-o"></i> My Statistics</a>';
						}?>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "top_all.php" ? ' class="active">' : '>'); ?>
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
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "list_rankup.php" ? ' class="active">' : '>'); ?>
						<a href="list_rankup.php"><i class="fa fa-fw fa-list-ul"></i> List Rankup</a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "battle_area.php" ? ' class="active">' : '>'); ?>
						<a href="battle_area.php"><span class="glyphicon glyphicon-fire" aria-hidden="true"></span> Battle Area</a>
					</li>
					<?PHP echo '<li'.(basename($_SERVER['SCRIPT_NAME']) == "info.php" ? ' class="active">' : '>'); ?>
						<a href="info.php"><i class="fa fa-fw fa-info-circle"></i> Ranksystem Info</a>
					</li>
				</ul>
			</div>
			<!-- /.navbar-collapse -->
		</nav>
<?PHP
function error_handling($msg,$type = NULL) {
	switch ($type) {
		case NULL: echo '<div class="alert alert-success alert-dismissible">'; break;
		case 1: echo '<div class="alert alert-info alert-dismissible">'; break;
		case 2: echo '<div class="alert alert-warning alert-dismissible">'; break;
		case 3: echo '<div class="alert alert-danger alert-dismissible">'; break;
	}
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>',$msg,'</div>';
}