<?php
	header('Content-type: text/html; charset=utf-8');
	setlocale(LC_TIME, 'de_DE');

	// CONNECT TO DATABASE
	require_once('config.php');
	$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	mysqli_set_charset($db, 'utf8');
	mysqli_select_db($db, DB_NAME);

	/* REGIONS */
	$result = mysqli_query($db, 'SELECT id,country,region,meta FROM `ferien_countries` ORDER BY id');
	$regions = array();
	$regionIDs = array();
	$tArray = array();
	$allDays = array();
	$regionMeta = array();
	
	/* DATE PREPARATIONS */
	// http://php.net/manual/en/function.date.php
	$today = date('Y-m-d');
	
	$requMonthDay = $today; // or date('Y-m-01'); // make it first of month
	if(isset($_POST['request']))
	{
		// sanitize string, keep only 0-9 and -
		// also make 2013-10 to 2013-10-01
		$requMonthDay = preg_replace("/[^0-9\-]/i", '', $_POST['request']).'-01';
	}
	
	// fill Arrays with data
	$output = '<table class="bordered">';
	$output .= '
			<tr>
				<th>id</th> 
				<th>country</th> 
				<th>region</th> 
				<th>meta</th> 
			</tr>';
	while($row = mysqli_fetch_assoc($result))
	{
		$output .= '
			<tr> 
				<td>'.$row['id'].'</td> 
				<td>'.$row['country'].'</td> 
				<td>'.$row['region'].'</td> 
				<td>'.$row['meta'].'</td>
			</tr>
		';
	}
	$output .= '</table>';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">

<head>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	
	<title>Schulferien Deutschland, Schweiz, Österreich</title>
	<meta name="description" content="Alle Schulferien auf einen Blick, für die Bundesländer in Deutschland und Österreich und Kantone der Schweiz. Auch zum Drucken." />
	
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400" type="text/css" />
	<link rel="stylesheet" href="styles.css?v=0.0.01" type="text/css" />
	
	<style type="text/css">
	.bordered tr td {
		background:#FFF !important;
	}
	</style>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="/js/jquery/3.2.1/jquery.min.js"><\/script>')</script>

</head>

<body>
<div id="wrap">
		<h1>Schulferien / REGIONEN </h1>
		
		<div class="sharebox">
			<a class="shlink tooltipS" title="Kurzlink zu dieser Seite" href="http://bit.ly/178dI4Z" onclick='_gaq.push(["_trackEvent", "Share-Minibox", "Shortlink"]);'></a>
			<a class="shprint tooltipS" title="Kalender drucken" href="javascript:window.print();" onclick='_gaq.push(["_trackEvent", "Share-Minibox", "Print"]);'></a>
			<a class="shfb tooltipS" title="Auf Facebook teilen" href="https://www.facebook.com/sharer.php?u=http%3A%2F%2Fwww.yoursite.com%2Fferien" onclick='_gaq.push(["_trackEvent", "Share-Minibox", "Facebook"]);'></a>
			<a class="shgp tooltipS" title="Auf Google+ teilen" href="https://plus.google.com/share?url=http%3A%2F%2Fwww.yoursite.com%2Fferien" onclick='_gaq.push(["_trackEvent", "Share-Minibox", "Google Plus"]);'></a>
			<a class="shtw tooltipS" title="Auf Twitter teilen" href="https://www.twitter.com/share?url=http%3A%2F%2Fwww.yoursite.com%2Fferien" onclick='_gaq.push(["_trackEvent", "Share-Minibox", "Twitter"]);'></a>
		</div>
			
		<?php
			echo $output;
		?>
		
</div>
				
	<?php include('inc_footer.php'); ?>

</body>
</html>

<?php
	/* More to come:
	- bei Regions, die aktuell Ferien haben (wenn auf Startseite, aktueller Monat), zeige Tipsy oder Regionname<br />noch x Tage Ferien / oder auch "noch x Tage bis zu den Ferien"
	- add embed widget
	- api?
	- tag meines Geburtstages berechnen, Feld ganz unten mit Jquery berechnen	
	*/
?>