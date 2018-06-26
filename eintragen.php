<?php
	header('Content-type: text/html; charset=utf-8');
	setlocale(LC_TIME, 'de_DE');

	// CONNECT TO DATABASE
	require_once('config.php');
	$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	mysqli_set_charset($db, 'utf8');
	mysqli_select_db($db, DB_NAME);

	// AJAX request
	$transferString = $_POST['ajaxdata'];
	$newdata = json_decode($transferString, true);
	if(!empty($newdata))
	{
		$newdata = str_replace('&quot;', '"', $newdata); // see stackoverflow.com/questions/3110487/
		$regionid = (int)trim($newdata['regionid']); // id
		$requY = date('Y'); // year
		
		// get all existing holidays 
		// Query to get all holiday periods of region in the requested year
		$result = mysqli_query($db, 'SELECT id,startdate,enddate,meta FROM `ferien_periods` 
								WHERE id="'.$regionid.'" 
								AND startdate > "2015-12-31"
								ORDER BY `startdate`');
								// WHERE id="'.$regionid.'" AND YEAR(`startdate`) = YEAR("'.$requY.'-01-01'.'")
								// OR id="'.$regionid.'" AND YEAR(`enddate`) = YEAR("'.$requY.'-01-01'.'")
		
		$tString = '';
		$listferien = '<ul>';
		while($row = mysqli_fetch_assoc($result))
		{
			$ferienmeta = $row['meta'];
			$bgcss = '';
			// do we have ferien in meta, then we color the background
			if(strpos($row['meta'],'ferien') !== false) {
				$bgcss = ' class="txthbg"';
			}
			
			if($row['startdate']==$row['enddate']){
				// display text for one day
				$listferien .= '<li><span'.$bgcss.'>'.date('d.m.Y', strtotime($row['startdate'])).' '.$ferienmeta.'</span></li>';
			}
			else {
				// display text for time frame
				$listferien .= '<li><span'.$bgcss.'>'.date('d.m.Y', strtotime($row['startdate'])).' - '.date('d.m.Y', strtotime($row['enddate'])).' '.$ferienmeta.'</span></li>';
			}
			if($tString=='') {
				// first entry without leading comma
				$tString .= $row['startdate'].','.$row['enddate'].','.$row['meta'];
			}
			else {
				$tString .= ','.$row['startdate'].','.$row['enddate'].','.$row['meta'];
			}
		}
		$listferien .= '</ul>';
		
		echo json_encode($listferien);
		exit();
	} // END AJAX ajaxdata
	
	// AJAX request
	$transferString = $_POST['eventdata'];
	$newdata = json_decode($transferString, true);
	if(!empty($newdata))
	{
		$newdata = str_replace('&quot;', '"', $newdata); // see stackoverflow.com/questions/3110487/
		$regionid = (int)trim($newdata['regionid']); // id
		$starttime = strip_tags(trim($newdata['start']));
		$endtime = strip_tags(trim($newdata['end']));
		$meta = strip_tags(trim($newdata['meta']));
		
		// error_log($regionid.' | '.$starttime.' | '.$endtime.' | '.$meta);
		
		$outstring  = '';
		
		if(isset($regionid, $starttime, $endtime, $meta))
		{
			$now = date('Y-m-d H:i:s', time());
			$ip = ip2long( get_ipaddress() );
			
			// check and parse date d.m.y 
			$strt = explode('.', $starttime);
			// construct yyyy-mm-dd with leading zero if necessary
			$startdate = str_pad($strt[2], 2, '0', STR_PAD_LEFT).'-'.str_pad($strt[1], 2, '0', STR_PAD_LEFT).'-'.str_pad($strt[0], 2, '0', STR_PAD_LEFT);
			$endt = explode('.', $endtime);
			$enddate = str_pad($endt[2], 2, '0', STR_PAD_LEFT).'-'.str_pad($endt[1], 2, '0', STR_PAD_LEFT).'-'.str_pad($endt[0], 2, '0', STR_PAD_LEFT);
			// error_log($now.' | '.$ip.' | '.$startdate.' | '.$enddate.' | '.$meta);
			
			// write proposed event to database
			// MYSQL will ignore duplicate since we set (createip, id, startdate, enddate, meta) UNIQUE
			// cmd was: ALTER TABLE ferien_proposals ADD UNIQUE INDEX(createip,id,startdate,enddate,meta);
			mysqli_query($db, 'INSERT IGNORE INTO `ferien_proposals` 
							(
								created, 
								createip, 
								regionid, 
								startdate, 
								enddate, 
								meta
							)
							VALUE ( 
								"'.mysqli_real_escape_string($db, $now).'", 
								"'.mysqli_real_escape_string($db, $ip).'", 
								"'.mysqli_real_escape_string($db, $regionid).'", 
								"'.mysqli_real_escape_string($db, $startdate).'", 
								"'.mysqli_real_escape_string($db, $enddate).'", 
								"'.mysqli_real_escape_string($db, $meta).'" 
							)
							');

			$regionname = get_regionname($db, $regionid); 

			$outstring .= '
				<tr>
					<td>'.$regionname.'</td>
					<td>'.$starttime.'</td>
					<td>'.$endtime.'</td>
					<td>'.$meta.'</td>
				</tr>
			'; 
			
		}
		else 
		{
			$outstring .= '
			<tr style="color:#F00;">Daten unvollständig.</tr>
			';
		}
		
		echo json_encode($outstring);
		exit();
	} // END AJAX eventdata
	
	// DEFAULT PAGE
	
	/* REGIONS */
	$result = mysqli_query($db, 'SELECT id,country,region,meta FROM `ferien_countries` 
							ORDER BY id');
	$regions = array();
	$regionIDs = array();
	$tArray = array();
	$allDays = array();
	$regionMeta = array();
	
	/* DATE PREPARATIONS */
	// php.net/manual/en/function.date.php
	$today = date('Y-m-d');
	
	$requMonthDay = $today; // or date('Y-m-01'); // make it first of month
	if(isset($_POST['request']))
	{
		// sanitize string, keep only 0-9 and -
		// also make 2013-10 to 2013-10-01
		$requMonthDay = preg_replace("/[^0-9\-]/i", '', $_POST['request']).'-01';
	}
	
	// fill Arrays with data
	$output = '';
	
	$output .=
	'
	<div class="choiceholder">
	
		<h4>
			1. Region auswählen
		</h4>
		<div class="choose_country">
			<p>Land auswählen:</p>
			<select name="country" id="country">
				<option value="de">Deutschland</option>
				<option value="at">Österreich</option>
				<option value="ch">Schweiz</option>
			</select>
		</div>
	';
	
		$output .= '
		<div class="choose_region">
			<p>
				Bundesland/Kanton auswählen:
			</p>
		';
	$list_de = '<select name="region_de" id="region_de">';
	$list_at = '<select name="region_at" id="region_at">';
	$list_ch = '<select name="region_ch" id="region_ch">';
	
	while($row = mysqli_fetch_assoc($result))
	{
		if($row['country']=='de')
		{
			$list_de .= '
				<option value="'.$row['id'].'">'.$row['region'].'</option>
			';
		}
		else if($row['country']=='at')
		{
			$list_at .= '
				<option value="'.$row['id'].'">'.$row['region'].'</option>
			';
		}
		else if($row['country']=='ch')
		{
			$list_ch .= '
				<option value="'.$row['id'].'">'.$row['region'].'</option>
			';
		}
	}
	$list_de .= '</select> ';
	$list_at .= '</select> ';
	$list_ch .= '</select> ';
	
	$output .= $list_de . $list_at . $list_ch;
	
	$output .= '
		</div> <!-- choose_region -->
	';

	$output .= '
	</div> <!-- choiceholder -->
	';

?><!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="index,follow" />
	<meta name="description" content="Hier kannst du die Ferien für Deutschland, Österreich und die Schweiz eintragen. Die Community erstellt die Daten für die Community." />
	
	<title>Schulferien eingeben - Deutschland, Schweiz, Österreich</title>
	
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400" type="text/css" />
	<link rel="stylesheet" href="styles.css?v=0.0.01" type="text/css" />
	
	<style type="text/css">
	.wrap {
		display:block;
		padding:20px 0 0 20px;
		min-height:500px;
	}
	h1 {
		margin-bottom:30px;
	}
	h3 {
		font-weight:normal;
	}
	h4 {
		margin:40px 0 0 0;
	}
	.choiceholder {
		width:100%;
		max-width:500px;
		vertical-align:top;
	}
	.choose_country {
		display:inline-block;
		width:35%;
	}
	.choose_region {
		display:inline-block;
		width:55%;
	}
	select, .entries {
		border:1px solid #CCC;
		padding:5px;
	}
	#knownregion_head {
		margin-top:40px;
	}
	#knownholidays {
		font-size:13px;
		line-height:150%;
	}
	.newentry_wrap {
		display:block;
		margin-top:50px;
	}
	.newentry_wrap {
	
	}
	.newentry_wrap label {
		display:table-row;
		margin:10px 0;
		height:20px;
	}
	.newentry_wrap label input {
		display:table-cell;
		margin-right:15px;
		margin-top:10px;
	}
	.newentry_wrap label input:focus {
		background:#FFE;
	}
	.newentry_wrap label span {
		display:table-cell;
		padding-right:15px;
		margin-top:10px;
	}
	.defaultbutton {
		display: inline-block;
		width: auto;
		padding: 7px 10px;
		text-align: center;
		overflow: visible;
		margin: 20px 0 10px 0;
		font-size: 12px;
		white-space: nowrap;
		cursor: pointer;
		outline: 0px none;
		border-radius: 0.2em;
		color: #FFF !important;
		text-shadow: none;
		border: 1px solid #33E;
		background: #44E none repeat scroll 0% 0%;
	}
	
	table#userentrytable {
		width:100%;
		max-width: 960px;
		margin-top:25px;
	}
	#userentrytable caption {
	  font-size: 15px;
	  font-weight: 400;
	  padding:10px 5px;
	  text-align:left;
	}
	#userentrytable thead th {
	  font-weight: 400;
	  background: #8a97a0;
	  color: #FFF;
	}
	#userentrytable tr {
	  background: #f4f7f8;
	  border-bottom: 1px solid #FFF;
	  margin-bottom: 5px;
	}
	#userentrytable tr:nth-child(even) {
	  background: #e8eeef;
	}
	#userentrytable th, td {
	  text-align: left;
	  padding: 5px 10px;
	  font-weight: 300;
	}
	#userentrytable tfoot tr {
	  background: none;
	}
	#userentrytable tfoot td {
	  padding: 10px 2px;
	  font-size: 0.8em;
	  font-style: italic;
	  color: #8a97a0;
	}


	</style>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="/js/jquery/3.2.1/jquery.min.js"><\/script>')</script>
	
	<script type="text/javascript" src="moment.min.js"></script>
	<script type="text/javascript" src="script.js?v=0.0.01"></script>

	<script type="text/javascript">
		$(document).ready(function()
		{
			$('#region_at, #region_ch, #knownregion_head').hide();
			
			$('#country').on('change', function() {
				$('#region_de, #region_at, #region_ch, #knownregion_head, #knownholidays').hide();
				if($(this).val()=='de') {
					$('#region_de').show();
				}
				else if($(this).val()=='at') {
					$('#region_at').show();
				}
				else if($(this).val()=='ch') {
					$('#region_ch').show();
				}
			});
			
			$('#region_de, #region_at, #region_ch').on('change', function() 
			{
				// empty former
				$('#knownholidays').show();
				$('#knownholidays').empty();
				// send data via ajax
				var regionid = $(this).val();
				var dataArray = {
					regionid: regionid,
				};
				// console.log('sending: regionid('+regionid+')');
				var senddata = JSON.stringify(dataArray);
				
				// ajax call to get known holidays
				$.ajax({
					type: 'POST',
					url: '/ferien/eintragen.php', // self
					dataType: 'json',
					data: { ajaxdata:senddata },
					cache: false,
					error: function(xhr, status, error) {
						console.log('oh unexpected, must be a server error');
					},
					success: function(data) {
						// console.log("server returned:"+data);
						var recentregion = $('option[value="'+getselectedregion()+'"]').text();
						// returns holiday data
						$('#knownholidays').html('<p style="font-weight:bold;font-size:14px;">'+recentregion+'</p>' + data);
						$('#knownregion_head').show();
						// add heading to entry inputs
						$('#regionselected').text( recentregion );
					}
				}); // end ajax
				
			}); // end on change
			
			$('#newentry_start, #newentry_end').on('keyup', function() 
			{
				var datestring = $(this).val();
				
				if(datestring == '') {
					return;
				}
				
				var checkerspan = $( '#'+$(this).attr('id')+'_check' );
				
				// check for correct format
				if( checkdateformat(datestring) )
				{
					if( moment(datestring, 'DD MM YYYY').isValid() ) {
						checkerspan.html('<span style="color:#0A0;">korrekt</span>');						
					}
					else {
						checkerspan.html('<span style="color:#F00;">Datum fehlerhaft</span>');						
					}
				}
				else 
				{
					checkerspan.html('<span style="color:#F00;">Format inkorrekt</span>');
				}
				
				// if(datestring.length < 10) {
				if(datestring.split('.').length-1 < 2 || datestring.length<8) {
					checkerspan.hide();
				}
				else {
					checkerspan.show();					
				}
			});
			
			$('#sendentry').click( function() { 
				// are all 3 fields filled out
				if($('#newentry_start').val()!='' && $('#newentry_end').val()!='' && $('#newentry_meta').val()!='')
				{
					// get regionid from selection
					// var country = $('#country').val();
					var regionid = getselectedregion();
					
					var starttime = $('#newentry_start').val();
					var endtime = $('#newentry_end').val();
					var meta = $('#newentry_meta').val();
					// send data via ajax
					var dataArray = {
						regionid: regionid,
						start: starttime,
						end: endtime,
						meta: meta,
					};
					console.log('sending: regionid: '+regionid);
					var senddata = JSON.stringify(dataArray);
					
					// ajax call to get known holidays
					$.ajax({
						type: 'POST',
						url: '/ferien/eintragen.php', // self
						dataType: 'json',
						data: { eventdata:senddata },
						cache: false,
						error: function(xhr, status, error) {
							console.log('oh unexpected, must be a server error');
						},
						success: function(data) {
							// console.log("server returned:"+data);
							// add entry to frontend 
							$('#userentrytable').append( data );
						}
					}); // end ajax
				}
				else 
				{
					$('#newentry_start').focus();
					alert('Bitte alle drei Felder ausfüllen.');
				}
			});
			
			// enter key: trigger #sendentry 
			$('#newentry_start, #newentry_end, #newentry_meta').keypress(function(e) {
				if(e.which == 13) {
					$('#sendentry').trigger('click');
				}
			});
			
			// startup
			$('#regionselected').text( $('option[value="'+getselectedregion()+'"]').text() );
			
		}); // end ready
		
function parsedate(value)
{
    var date = value.split("-");
    var d = parseInt(date[0], 10),
        m = parseInt(date[1], 10),
        y = parseInt(date[2], 10);
    return new Date(y, m - 1, d);
}

function getselectedregion() 
{
	var regionid = 0; 
	if($('#region_de').is(':visible')) {
		regionid = $('#region_de').val();						
	}
	else if($('#region_at').is(':visible')) {
		regionid = $('#region_at').val();						
	}
	else if($('#region_ch').is(':visible')) {
		regionid = $('#region_ch').val();						
	}
	return regionid;
}

function checkdateformat(dateString)
{
	// dd.mm.yyyy - //stackoverflow.com/a/8937460/1066234
	var m = dateString.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/);
	return (m) ? new Date(m[3], m[2]-1, m[1]) : null;	

	// yyyy-mm-dd - //stackoverflow.com/a/18759013/1066234
	/*var regEx = /^\d{4}-\d{2}-\d{2}$/;
	return dateString.match(regEx) != null;
	*/
} // end checkdateformat

	</script>
</head>

<body>
	<div class="wrap">
			<h1>
				<a href="/ferien/eintragen.php">Schulferien und Feiertage eingeben für 2017, 2018, …</a>
			</h1>
			
			<p>
				Sie können hier die Schulferien und Feiertage eingeben, <span style="color:#F00;">die dem öffentlichen Ferienkalender noch fehlen</span>.
			</p>
			<p>
				Wir prüfen alle Eingaben und schalten sie für den <a href="/ferien/">Ferienkalender Plus</a> frei.
			</p>
			<p>
				Vielen Dank für Ihre Hilfe. Nur durch Sie kann dieses Projekt weiter existieren.
			</p>

			<?php
				echo $output;
			?>
			
			<p id="knownregion_head">
				In unserer Datenbank veröffentlichte Feiertage und <span class="txthbg">Ferien</span> für diese Region ab 01.01.2016:
			</o>
			<div id="knownholidays"></div>
			
			<div class="newentry_wrap">
			
				<h4>
					2. Eintrag hinzufügen
				</h4>
				<p>
					Hinweis: Bei einem einzelnen Feiertag sind Start- und Enddatum gleich einzugeben.
				</p>
				<p style="font-weight:bold;margin:30px 0 0 0;">
					Eintrag für <span id="regionselected"></span>: 
				</p>
				<label for="enternew">
					<span>Start der Ferien:</span>
					<input class="entries" type="text" id="newentry_start" name="newentry_start" value="" placeholder="z. B. 03.05.2017" />
					<span id="newentry_start_check"></span>
				</label>
				<label for="enternew">
					<span>Ende der Ferien:</span>
					<input class="entries" type="text" id="newentry_end" name="newentry_end" value="" placeholder="z. B. 15.05.2017" />
					<span id="newentry_end_check"></span>
				</label>
				<label for="enternew">
					<span>Feriengrund/Feiertag:</span>
					<input class="entries" type="text" id="newentry_meta" name="newentry_meta" value="" placeholder="z. B. Pfingstmontag" />
				</label>
				<button id="sendentry" class="defaultbutton">Absenden</button>
				
			</div> <!-- newentry_wrap -->
			
			
			<div id="userentries">
				<table id="userentrytable">
					<caption>Ihre Einträge:</caption>
					<tr>
						<td>Region</td>
						<td>Start</td>
						<td>End</td>
						<td>Feriengrund/Feiertag</td>
					</tr>
					<?php
						$ip = ip2long(get_ipaddress());
						// get existing entries by IP
						$exentries = mysqli_query($db, '
												SELECT regionid,startdate,enddate,meta FROM `ferien_proposals` 
												WHERE createip="'.mysqli_real_escape_string($db, $ip).'" 
												ORDER BY `created`
												');
						
						$entriesstring = '';
						while($row = mysqli_fetch_assoc($exentries))
						{
							$entriesstring .= '
								<tr>
								<td>'.get_regionname($db, $row['regionid']).'</td>
								<td>'.date('d.m.Y', strtotime($row['startdate'])).'</td>
								<td>'.date('d.m.Y', strtotime($row['enddate'])).'</td>
								<td>'.$row['meta'].'</td>
								</tr>
							';
						}
						
						echo $entriesstring;
					?>
				</table>
			</div> <!-- userentries -->
			
			
	</div> <!-- wrap -->
					
	<?php include('inc_footer.php'); ?>

</body>
</html>

<?php
	
	function get_ipaddress()
	{
		if( isset( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) ){
			$ip = $_SERVER ['HTTP_X_FORWARDED_FOR'];
		}
		elseif( isset( $_SERVER ['HTTP_VIA'] ) ){
			$ip = $_SERVER ['HTTP_VIA'];
		}
		elseif( isset( $_SERVER ['REMOTE_ADDR'] ) ){
			$ip = $_SERVER ['REMOTE_ADDR'];
		}
		else{
			$ip = 0;
		}
		return $ip;
	}

	function get_regionname($db, $regionid)
	{ 
		$region_query = mysqli_query($db, 'SELECT `region` FROM `ferien_countries` 
									WHERE id = '.$regionid.'
									');
		return mysqli_result($region_query,0);
	}

	function mysqli_result($res,$row=0,$col=0)
	{ 
		$numrows = mysqli_num_rows($res); 
		if ($numrows && $row <= ($numrows-1) && $row >=0){
			mysqli_data_seek($res,$row);
			$resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
			if (isset($resrow[$col])){
				return $resrow[$col];
			}
		}
		return false;
	}
	
?>