<?php
	header('Content-type: text/html; charset=utf-8');
	setlocale(LC_TIME, 'de_DE.UTF8');
	$today = date('Y-m-d');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">

<head>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />

	<?php 
		echo '<title>Wochentag vom Geburtstag berechnen | Ferienkalender Plus</title>';
	?>

	<meta name="description" content="Berechne die Tage bis zu deinem Geburtstag. ***" />
	
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400" type="text/css" />
	<link rel="stylesheet" href="styles.css?v=0.0.02" type="text/css" />
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="/js/jquery/3.2.1/jquery.min.js"><\/script>')</script>
	
	<script type="text/javascript" src="script.js?v=0.0.02"></script>

	<style type="text/css">
		#main {
			margin-left:20px;
		}
	</style>
	
</head>

<body class="birthdayp">

	<div id="nav">
		<div id="fastaccess">
			<!-- <a id="datepickbtn">Weitere <input id="datepicker" name="request" type="text" value="<?php echo substr($requY,0,7); ?>" /></a> -->
			<a style="margin-left:5px;" href="/ferien/">« zurück zum Ferienkalender Plus</a>
		</div>
		
		<div id="calholdr">
			<div class="calendar"><?php echo substr($today,8,2); ?><em><?php echo strftime('%b %Y', strtotime($today)); ?></em></div>
			<div id="clock"></div>
		</div>
		<br />
	
		<div class="sharebox">
			<a class="shlink tooltipS" title="Kurzlink zu dieser Seite" href="http://bit.ly/178dI4Z" onclick='_gaq.push(["_trackEvent", "Ferien-Share", "Shortlink"]);'></a>
			<a class="shprint tooltipS" title="Kalender drucken" href="javascript:window.print();" onclick='_gaq.push(["_trackEvent", "Ferien-Share", "Print"]);'></a>
			<a class="shfb tooltipS" title="Auf Facebook teilen" href="https://www.facebook.com/sharer.php?u=http%3A%2F%2Fwww.yoursite.com%2Fferien" onclick='_gaq.push(["_trackEvent", "Share-Minibox", "Facebook"]);'></a>
			<a class="shgp tooltipS" title="Auf Google+ teilen" href="https://plus.google.com/share?url=http%3A%2F%2Fwww.yoursite.com%2Fferien" onclick='_gaq.push(["_trackEvent", "Ferien-Share", "Google Plus"]);'></a>
			<a class="shtw tooltipS" title="Auf Twitter teilen" href="https://www.twitter.com/share?url=http%3A%2F%2Fwww.yoursite.com%2Fferien" onclick='_gaq.push(["_trackEvent", "Ferien-Share", "Twitter"]);'></a>
		</div>
		
	</div>
		
	<div id="main">
	
		<h2>Wie viele Tage sind es noch bis zu meinem Geburtstag?</h2>
			<label for="bday">Geburtstag (Format Monat/Tag/Jahr): </label>
			<input type="text" name="bday" id="bday" value="10/15/1999" />
			<input type="submit" id="submit" value="Berechne" />
			<div id="message-box">&nbsp;</div>

		<script type="text/javascript">
			var validDatePattern = /^(0[1-9]|1[012])[\/](0[1-9]|[12][0-9]|3[01])[\/](19|20)\d\d$/;
			var daysTil = 0;

			$(document).ready(function() {
				var $_messageBox = $("#message-box");
				var message = "";
				
				$("#submit").on("click", function(e) {
					var today = new Date();
					var thisYear = today.getFullYear();
					var nextYear = thisYear + 1;
					
					var birthDate = $("#bday").val();
					
					if ( !validDatePattern.test(birthDate)) {
						$_messageBox.html("Error: Invalid Date");
						return false;
					}
					
					var birthDateParts = birthDate.split("/");
					var birthDay = new Date(thisYear, birthDateParts[0]-1, birthDateParts[1]);

					if (today.getTime() > birthDay.getTime()) {
						birthDay = new Date(nextYear, birthDateParts[0]-1, birthDateParts[1]);
					}
					
					daysTil = Math.floor((today.getTime() - birthDay.getTime()) / (3600*24*1000)) * -1;
					
					if (daysTil >= 365) {
						message = "Happy Birthday!!!";
					} else if (daysTil == 1) {
						message = "Noch "+daysTil+" Tag bis zu deinem Geburtstag.";
					} else {
						message = "Noch "+daysTil+" Tage bis zu deinem Geburtstag.";
					}
					
					$_messageBox.html(message);
				});
				
			});
			
			var month = new Array("Jan","Feb","Mrz","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
			var startyear = "1913";
			var endyear = "2013";

			function dayborn() {
				var month = document.bornday.birthmonth.options[document.bornday.birthmonth.selectedIndex].value;
				var day = document.bornday.birthday.options[document.bornday.birthday.selectedIndex].value;
				var year = document.bornday.birthyear.options[document.bornday.birthyear.selectedIndex].value;
				var birthday = new Date(year,month,day);
				var dayborn = birthday.getDay();
				var weekday = new Array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");

				$('#bornon').html("Du wurdest an einem " + weekday[dayborn] + " geboren.");
			}
			// script is not yt catching stupid inputs such as 30.Feb.
		</script>
		
		
		<h2>An welchem Wochentag habe ich Geburtstag?</h2>
		<p>Geburtstag angeben: </p>
		<form name="bornday">
			<table>
			<tr>
			<td>
				<select name="birthmonth" size="1">
				<script language="javascript">
				for(var i=0;i<month.length;i++)
				document.write("<option value="+i+">"+month[i]+"</option>");
				</script>
				</select></td>
			<td>
			<select name="birthday" size="1">
			<script language="javascript">
				for(var j=1;j<32;j++)
				document.write("<option value="+j+">"+j+"</option>");
				</script>
				</select></td>

			<td>
			<select name="birthyear" size="1">
			<script language="javascript">
				for(var k=startyear;k<endyear;k++)
				document.write("<option value="+k+">"+k+"</option>");
				</script>
				</select></td>
				</tr>
			  </table>
			  <p>
				<input value="Wochentag berechnen" onclick="dayborn()" type="button" />
			  </p>
		</form>

		<p id="bornon"></p>
		
	</div>
				
	<div id="footer">
		<!-- YOUR FOOTER -->
	</div>
	
</body>
</html>