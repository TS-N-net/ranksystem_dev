<?PHP
session_start();
$starttime = microtime(true);

require_once('../other/config.php');
require_once('../ts3_lib/TeamSpeak3.php');
require_once('../lang.php');
require_once('../other/session.php');

if(!isset($_SESSION['tsuid'])) {
	$hpclientip = ip2long($_SERVER['REMOTE_ADDR']);
	set_session_ts3($hpclientip, $ts['voice'], $mysqlcon, $dbname);
}

if ($substridle == 1) {
	$dbdata = $mysqlcon->query("SELECT s.uuid,s.count_week,s.idle_week,u.name,u.online,u.grpid FROM $dbname.stats_user AS s INNER JOIN $dbname.user AS u ON s.uuid = u.uuid WHERE s.removed='0' ORDER BY (s.count_week - s.idle_week) DESC");
} else {
	$dbdata = $mysqlcon->query("SELECT s.uuid,s.count_week,s.idle_week,u.name,u.online,u.grpid FROM $dbname.stats_user AS s INNER JOIN $dbname.user AS u ON s.uuid = u.uuid WHERE s.removed='0' ORDER BY s.count_week DESC");
}
$sumentries = $dbdata->rowCount() - 10;
$db_arr = $dbdata->fetchAll();
$count10 = 0;
$top10_sum = 0;
$top10_idle_sum = 0;


foreach ($db_arr as $client) {
	$sgroups  = explode(",", $client['grpid']);
	if (!in_array($client['uuid'], $exceptuuid) && !array_intersect($sgroups, $exceptgroup)) {
		if ($count10 == 10) break;
		if ($substridle == 1) {
			$hours = $client['count_week'] - $client['idle_week'];
		} else {
			$hours = $client['count_week'];
		}
		$top10_sum = round(($client['count_week']/3600)) + $top10_sum;
		$top10_idle_sum = round(($client['idle_week']/3600)) + $top10_idle_sum;
		$client_data[$count10] = array(
		'name'		=>	$client['name'],
		'count'		=>	$hours,
		'online'	=>	$client['online']
		);
		$count10++;
	}
}

$all_sum_data = $mysqlcon->query("SELECT SUM(count_week) FROM $dbname.stats_user");
$all_sum_data_res = $all_sum_data->fetchAll();
$others_sum = round(($all_sum_data_res[0][0]/3600)) - $top10_sum;

$all_idle_sum_data = $mysqlcon->query("SELECT SUM(idle_week) FROM $dbname.stats_user");
$all_idle_sum_data_res = $all_idle_sum_data->fetchAll();
$others_idle_sum = round(($all_idle_sum_data_res[0][0]/3600)) - $top10_idle_sum;

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
					<div class="col-lg-12">
						<h1 class="page-header">
							Top Users
							<small>Of The Week</small>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[0]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[0]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[0]['count']/3600))?> Hours Online Time</div>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[1]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[1]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[1]['count']/3600))?> Hours Online Time</div>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[2]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[2]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[2]['count']/3600))?> Hours Online Time</div>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[3]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[3]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[3]['count']/3600))?> Hours Online Time</div>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[4]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[4]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[4]['count']/3600))?> Hours Online Time</div>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[5]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[5]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[5]['count']/3600))?> Hours Online Time</div>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[6]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[6]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[6]['count']/3600))?> Hours Online Time</div>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[7]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[7]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[7]['count']/3600))?> Hours Online Time</div>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[8]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[8]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[8]['count']/3600))?> Hours Online Time</div>
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
										<div class="huge"><span title=<?PHP echo '"' .$client_data[9]['name'] .'"'?>><?PHP echo str_replace(' ', '', $client_data[9]['name']) ?></span></div>
										<div>With <?PHP echo round(($client_data[9]['count']/3600))?> Hours Online Time</div>
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
						<h4>#1 <?PHP echo $client_data[0]['name'] ?><?PHP echo ($client_data[0]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[0]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: 100%;"><?PHP echo '<font color="#000000">' .round(($client_data[0]['count']/3600)) .' Hours (Defines 100 %)</font>'?>
							</div>
						</div>
						<h4>#2 <?PHP echo $client_data[1]['name'] ?><?PHP echo ($client_data[1]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[1]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[1]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[1]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($client_data[1]['count']/3600)) .' Hours (' .get_percentage($client_data[0]['count'], $client_data[1]['count']) .' % - Compared To #1)</font>'?>
							</div>
						</div>
						<h4>#3 <?PHP echo $client_data[2]['name'] ?><?PHP echo ($client_data[2]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[2]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[2]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[2]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($client_data[2]['count']/3600)) .' Hours (' .get_percentage($client_data[0]['count'], $client_data[2]['count']) .' % - Compared To #1)</font>'?>
							</div>
						</div>
						<h4>#4 <?PHP echo $client_data[3]['name'] ?><?PHP echo ($client_data[3]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[3]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[3]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[3]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($client_data[3]['count']/3600)) .' Hours (' .get_percentage($client_data[0]['count'], $client_data[3]['count']) .' % - Compared To #1)</font>'?>
							</div>
						</div>
						<h4>#5 <?PHP echo $client_data[4]['name'] ?><?PHP echo ($client_data[4]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[4]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[4]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[4]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($client_data[4]['count']/3600)) .' Hours (' .get_percentage($client_data[0]['count'], $client_data[4]['count']) .' % - Compared To #1)</font>'?>
							</div>
						</div>
						<h4>#6 <?PHP echo $client_data[5]['name'] ?><?PHP echo ($client_data[5]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[5]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[5]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[5]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($client_data[5]['count']/3600)) .' Hours (' .get_percentage($client_data[0]['count'], $client_data[5]['count']) .' % - Compared To #1)</font>'?>
							</div>
						</div>
						<h4>#7 <?PHP echo $client_data[6]['name'] ?><?PHP echo ($client_data[6]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-warning progress-bar-striped <?PHP echo ($client_data[6]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[6]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[6]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($client_data[6]['count']/3600)) .' Hours (' .get_percentage($client_data[0]['count'], $client_data[6]['count']) .' % - compared to #1)</font>'?>
							</div>
						</div>
						<h4>#8 <?PHP echo $client_data[7]['name'] ?><?PHP echo ($client_data[7]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-danger progress-bar-striped <?PHP echo ($client_data[7]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[7]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[7]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($client_data[7]['count']/3600)) .' Hours (' .get_percentage($client_data[0]['count'], $client_data[7]['count']) .' % - Compared To #1)</font>'?>
							</div>
						</div>
						<h4>#9 <?PHP echo $client_data[8]['name'] ?><?PHP echo ($client_data[8]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-striped <?PHP echo ($client_data[8]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[8]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[8]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($client_data[8]['count']/3600)) .' Hours (' .get_percentage($client_data[0]['count'], $client_data[8]['count']) .' % - Compared To #1)</font>'?>
							</div>
						</div>
						<h4>#10 <?PHP echo $client_data[9]['name'] ?><?PHP echo ($client_data[9]['online'] == '1') ? ' (Status: <font color="#00FF00">Online</font>)' : ' (Status: <font color="#FF0000">Offline</font>)' ?></h4>
						<div class="progress">
							<div class="progress-bar progress-bar-success progress-bar-striped <?PHP echo ($client_data[9]['online'] == '1') ? 'active' : '' ?>" role="progressbar" aria-valuenow="<?PHP echo get_percentage($client_data[0]['count'], $client_data[9]['count']) ?>" aria-valuemin="0" aria-valuemax="100" style="min-width: 20em;width: <?PHP echo get_percentage($client_data[0]['count'], $client_data[9]['count']) ?>%"><?PHP echo '<font color="#000000">' .round(($client_data[9]['count']/3600)) .' Hours (' .get_percentage($client_data[0]['count'], $client_data[9]['count']) .' % - Compared To #1)</font>'?>
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
	</script>
</body>

</html>