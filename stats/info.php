<?PHP
session_start();
$starttime = microtime(true);

require_once('../other/config.php');
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
						<p>The <a href="http://ts-n.net/ranksystem.php" target="_blank">Ranksystem</a> was coded by <strong>Newcomer1989</strong> Copyright &copy 2009-2016 <a href="http://ts-n.net/" target="_blank">TeamSpeak Sponsoring TS-N.NET</a>. All rights reserved.</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><font color="#f0ad4e">When Did You Create The Ranksystem?</font></strong></h4>
						<p>First alpha release: 05/10/2014.</p>
						<p>First beta release: 01/02/2015.</p>
						<p>You can see the newest version on the <a href="http://ts-n.net/ranksystem.php" target="_blank">Ranksystem Website</a>.</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><font color="#d9534f">How Did You Create The Ranksystem?</font></strong></h4>
						<p>The Ranksystem is coded in</p>
						<p><a href="http://php.net/" target="_blank">PHP</a> - Copyright &copy 2001-2016 the <a href="https://secure.php.net/credits.php" target="_blank">PHP Group</a></p><br>
						<p>It uses also the following libraries:</p>
						<p><a href="http://jquery.com/" target="_blank">jQuery v2.2.0</a> - Copyright &copy 2016 The jQuery Foundation</p> 
						<p>jQuery Autocomplete plugin 1.1 - Copyright (c) 2009 J&ouml;rn Zaefferer</p> 
						<p><a href="http://fontawesome.io" target="_blank">Font Awesome 4.2.0</a> - Copyright &copy davegandy</p>
						<p><a href="http://jquery.com/plugins/project/ajaxqueue" target="_blank">Ajax Queue Plugin</a> - Copyright &copy 2013 Corey Frang</p> 
						<p><a href="http://planetteamspeak.com/" target="_blank">TeamSpeak 3 PHP Framework 1.1.23</a> - Copyright &copy 2010 Planet TeamSpeak</p> 
						<p><a href="http://getbootstrap.com/" target="_blank">Bootstrap 3.3.6</a> - Copyright &copy 2011-2016 Twitter, Inc.</p>
						<p><a href="http://morrisjs.github.io/morris.js/" target="_blank">morris.js</a> - Copyright &copy 2013 Olly Smith</p>
						<p><a href="http://raphaeljs.com" target="_blank">Rapha&euml;l 2.1.4 - JavaScript Vector Library</a> - Copyright © 2008-2012 Dmitry Baranovskiy</p>
						<p><a href="http://startbootstrap.com" target="_blank">SB Admin Bootstrap Admin Template</a> - Copyright &copy 2013-2016 Blackrock Digital LLC.</p>
						<br>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<h4><strong><font color="#337ab7">Special Thanks To:</font></strong></h4>
						<p><a href="http://nya-pw.ru/" target="_blank">sergey</a> - for russian translation</p>
						<p>Bejamin Frost - for initialisation the bootstrap design</p>
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