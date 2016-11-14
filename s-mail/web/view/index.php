<?php
	$root = "..";
	include($root."/common.php");
	
	if ($auth != 1) { header("Location: ".$webp); die; }
	
	$hpag[0] = "opacity: 1.0 !important;";
	$user = $_SESSION["auth"][0];
	
	$udir = ($mail."/".$user);
	
	$data = file_get_contents($udir."/".$user.".auth");
	$list = explode("\n", $data);
	if (count($list) > 3)
	{
		$pkey = base64_decode($list[3]);
		$pkey = str_replace("\r", "", $pkey);
		$pkey = str_replace("\n", "", $pkey);
	}
	
	$emailmsg = getemail($_GET["e"], $udir);
	$file = $emailmsg[0];
	$email = $emailmsg[1];
	$smails = $emailmsg[2];
	$fnames = $emailmsg[3];
	
	if (isset($_GET["m"]))
	{
		if ($_GET["m"] == "read") { $repl = ".read"; }
		if ($_GET["m"] == "true") { $repl = ".read"; if (preg_match("/^.*\.read(|\..*)$/i", $email)) { $repl = ""; } }
		$fnew = preg_replace("/^(.*\.[0-9a-f]{8,})(|\.sent|\.trash)$/i", "$1".$repl."$2", preg_replace("/\.read/i", "", $email));
		if ($fnew != $email)
		{
			rename($udir."/".$email, $udir."/".$fnew);
		}
		if ($_GET["m"] == "true") { die; }
	}
	
	if (isset($_GET["d"]))
	{
		if ($_GET["d"] == "true") { $repl = ".trash"; if (preg_match("/^.*\.trash$/i", $email)) { $repl = ""; } }
		$fnew = preg_replace("/^(.*)(|\.trash)$/i", "$1".$repl, preg_replace("/\.trash/i", "", $email));
		if ($fnew != $email)
		{
			rename($udir."/".$email, $udir."/".$fnew);
		}
		if ($_GET["d"] == "true") { die; }
	}
?>

<html>
	<script>
		var fnames = "<?php print($fnames); ?>";
	</script>
	
	<?php include($root."/html/head.html"); ?>
	
	<body onload="getkeyi(user, '#keyi'); keypre(); msgdec('view');">
		<?php include($root."/html/menu.html"); ?>
		
		<center>
			<div class="divtable" style="width: 85% !important;"><table class="table table-condensed" style="color: #333;">
				<thead>
					<tr><th colspan="2" style="border: 0px;">
						<table style="width: 100%; color: #333;"><tr>
							<th style="text-align: left; width: 30%;">
								<button type="button" class="btn btn-sm btn-danger" onclick="javascript:window.history.back();"><span class="glyphicon glyphicon-circle-arrow-left" style="top: 2px;"></span> &nbsp; Back</button>
							</th>
							<th style="text-align: center;">Message for &nbsp; <i>[ <?php print($user."@".$name); ?> ]</i></th>
							<th style="text-align: right; width: 30%;">
								<button type="button" class="btn btn-sm btn-success" onclick="location.href = '<?php print($webp); ?>/make/';"><span class="glyphicon glyphicon-plus-sign" style="top: 2px;"></span> &nbsp; New</button>
								 &nbsp; 
								<button type="button" class="btn btn-sm btn-warning" onclick="markmail('<?php print($file); ?>', 'm', function() { window.history.back(); });"><span class="glyphicon glyphicon-certificate" style="top: 2px;"></span> / Old</button>
								 &nbsp; 
								<button type="button" class="btn btn-sm btn-danger" onclick="markmail('<?php print($file); ?>', 'd', function() { window.history.back(); });"><span class="glyphicon glyphicon-trash" style="top: 2px;"></span> / Undo</button>
							</th>
						</tr></table>
					</th></tr>
					<tr><th colspan="2" style="text-align: center; border: 0px;">
						<a href="javascript:window.location.href = '<?php print($webp); ?>/make/?e=<?php print($file); ?>&m=r'">Reply</a>
						 &nbsp; 
						<a href="javascript:window.location.href = '<?php print($webp); ?>/make/?e=<?php print($file); ?>&m=a'">Reply All</a>
						 &nbsp; 
						<a href="javascript:window.location.href = '<?php print($webp); ?>/make/?e=<?php print($file); ?>&m=f'">Forward</a>
					</th></tr>
				</thead>
				<tbody id="mail">
					<tr><td style="width: 0px; white-space: nowrap; text-align: right; border: 0px;"><b><span>Date:</span></b></td><td style="width: 100%; text-align: left; border: 0px;"><span id="date"> &nbsp; </span></td></tr>
					<tr><td style="width: 0px; white-space: nowrap; text-align: right; border: 0px;"><b><span>From:</span></b></td><td style="width: 100%; text-align: left; border: 0px;"><span id="from"> &nbsp; </span></td></tr>
					<tr><td style="width: 0px; white-space: nowrap; text-align: right; border: 0px;"><b><span>To:</span></b></td><td style="width: 100%; text-align: left; border: 0px;"><span id="dest"> &nbsp; </span></td></tr>
					<tr><td style="border: 0px;"> &nbsp; </td><td style="width: 100%; text-align: left; border: 0px;" id="attach"></td></tr>
					<tr><td colspan="2" style="border: 0px;"> &nbsp; </td></tr>
					<tr><td colspan="2" style="border: 0px;"><b><span id="subj"> &nbsp; </span></b></td></tr>
					<tr><td colspan="2" style="border: 0px;"> &nbsp; </td></tr>
					<tr><td colspan="2" style="border: 0px;"><span id="mesg"> &nbsp; </span></td></tr>
				</tbody>
			</table></div>
		</center>
		
		<?php include($root."/html/foot.html"); ?>
	</body>
</html>
