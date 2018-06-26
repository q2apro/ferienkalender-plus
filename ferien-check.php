<?php
	header('Content-type: text/html; charset=utf-8');
	setlocale(LC_TIME, 'de_DE');

	// CONNECT TO DATABASE
	require_once('config.php');
	$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	mysqli_set_charset($db, 'utf8');
	mysqli_select_db($db, DB_NAME);
	
	/* REGIONS */
	$result = mysqli_query($db, 'SELECT id,country,region,meta FROM `ferien_countries` 
							ORDER BY id');
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
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	
	<title>Admin: Schulferien Proposals</title>
	
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400" type="text/css" />
	<link rel="stylesheet" href="styles.css?v=0.0.01" type="text/css" />
	
	<style type="text/css">
	.doublefinding td {
		color:#00F;
	}
	.newentryfinding td {
		color:#F00;
	}
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
		max-width: 1280px;
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
	#userentrytable tr:hover {
		background:#FFA;
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
						url: siteurl+'ferien/eintragen.php', // self
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
			});
			
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
			// dd.mm.yyyy - http://stackoverflow.com/a/8937460/1066234
			var m = dateString.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/);
			return (m) ? new Date(m[3], m[2]-1, m[1]) : null;	

		} // end checkdateformat

	</script>
</head>

<body>
	
	<div id="userentries">
		<table id="userentrytable">
			<caption>Einträge:</caption>
			<tr>
				<td>ID</td>
				<td>Region</td>
				<td>Start</td>
				<td>End</td>
				<td>Feriengrund/Feiertag</td>
				<td>Check Result</td>
				<td>Action</td>
			</tr>
			<?php
				$ip = ip2long(get_ipaddress());
				// get existing entries by IP
				$exentries = mysqli_query($db, '
										SELECT id,regionid,startdate,enddate,meta FROM `ferien_proposals` 
										# WHERE 
										ORDER BY regionid, startdate, enddate 
										');
				
				$entriesstring = '';
				$startdate_former = '';
				$enddate_former = '';
				while($row = mysqli_fetch_assoc($exentries))
				{
					$entryid = $row['id'];
					$regionid = $row['regionid'];
					$startdate = $row['startdate'];
					$enddate = $row['enddate'];
					$result = '';
					$buttons = '
					<a href="#">Delete</a>
					';
					
					$cssmarker = '';
					if($startdate_former==$startdate && $enddate_former==$enddate)
					{
						// check if entry exist in ferien table already 
						$checkexisting = mysqli_query($db, '
										SELECT id,startdate,enddate,meta FROM `ferien_periods` 
										WHERE id = "'.$regionid.'"
										AND startdate = "'.$startdate.'" 
										AND enddate = "'.$enddate.'" 
										ORDER BY id, startdate, enddate 
										');
						$entry = mysqli_fetch_assoc($checkexisting); 
						if(isset($entry))
						{
							$result = 'Existiert als: "'.$entry['meta'].'"';								
							$cssmarker = ' class="doublefinding"';
						}
						else {
							$result = 'Neuer Eintrag!';
							$cssmarker = ' class="newentryfinding"';
							$buttons .= ' | <a href="#">Übernehmen?</a>';
						}
					}
					$entriesstring .= '
						<tr'.$cssmarker.' id="'.$entryid.'">
							<td>'.$entryid.'</td>
							<td>'.get_regionname($db, $row['regionid']).'</td>
							<td>'.$startdate.'</td>
							<td>'.$enddate.'</td>
							<td>'.$row['meta'].'</td>
							<td>'.$result.'</td>
							<td>'.$buttons.'</td>
						</tr>
					';
					$startdate_former = $startdate; 
					$enddate_former = $enddate; 
				}
				
				echo $entriesstring;
			?>
		</table>
	</div> <!-- userentries -->
	
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