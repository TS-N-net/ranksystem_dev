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
require_once('nav.php');
?>
		<div id="page-wrapper">
<?PHP if(isset($err_msg)) error_handling($err_msg, 3); ?>
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
						<p>A TS3 Bot, which gathers information and statistics about every user and displays the result on <strong>this</strong> site.
						</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><font color="#5cb85c">Who Created The Ranksystem?</font></strong></h4>
						<p>The Ranksystem was coded by <strong>Newcomer1989</strong> from the TeamSpeak Sponsoring <a href="http://ts-n.net/" target="_blank">TS-N.NET</a>.</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><font color="#f0ad4e">When Did You Create The Ranksystem?</font></strong></h4>
						<p>First alpha release: 05/10/2014 (dd/mm/yyyy).</p>
						<p>First beta release: 01/02/2015 (dd/mm/yyyy).</p>
						<p>You can see the newest version on the <a href="http://ts-n.net/ranksystem.php" target="_blank">Ranksystem Website</a>.</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><font color="#d9534f">How Did You Create The Ranksystem?</font></strong></h4>
						<p>The Ranksystem is coded in PHP.</p>
						<p>Its use the following other scripts:</p>
						<p><a href="http://planetteamspeak.com/" target="_blank">TeamSpeak 3 PHP Framework</a> - for TeamSpeak Query handling</p> 
						<p><a href="http://getbootstrap.com/" target="_blank">Bootstrap</a> - for design on this page.</p>
						<p><a href="http://morrisjs.github.io/morris.js/" target="_blank">morris.js</a> - Copyright (c) 2013, Olly Smith - Bootstrap charts on this site</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><font color="#337ab7">Special Thanks To:</font></strong></h4>
						<p><a href="http://nya-pw.ru/" target="_blank">sergey</a> - for russian translation</p>
						<p><a href="http://ts3botbysicariius.dx.am/" target="_blank">Bejamin Frost</a> - for initialisation the bootstrap design</p>
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