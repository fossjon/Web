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
	
	$file = saferstr($_GET["e"], $unchrs);
	$file = preg_replace("/\.trash$/i", "", $file);
	$file = preg_replace("/\.sent$/i", "", $file);
	$file = preg_replace("/\.read$/i", "", $file);
	
	$emails = scandir($udir);
	$fnames = "files ";
	foreach ($emails as $email)
	{
		if (($email == ".") || ($email == "..")) { continue; }
		if (preg_match("/^.*auth$/i", $email)) { continue; }
		if (preg_match("/^".$file.".*$/i", $email))
		{
			if (isset($_GET["m"]))
			{
				if ($_GET["m"] == "read") { $repl = ".read"; }
				if ($_GET["m"] == "true") { $repl = ".read"; if (preg_match("/^.*\.read(|\..*)$/i", $email)) { $repl = ""; } }
				$fnew = preg_replace("/^(.*\.[0-9a-f]{8,})(|\.sent|\.trash)$/i", "$1".$repl."$2", preg_replace("/\.read/i", "", $email));
				if ($fnew != $email)
				{
					rename($udir."/".$email, $udir."/".$fnew);
					$email = $fnew;
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
					$email = $fnew;
				}
				if ($_GET["d"] == "true") { die; }
			}
			
			$data = file_get_contents($udir."/".$email);
			$list = explode("\n", $data);
			if (count($list) > 5)
			{
				$smails = ("['".$list[0]."', '".$list[1]."', '".$list[2]."', '".$list[3]."', '".trim($list[4])."', '".$email."', '".trim($list[5])."']");
				$files = explode(" ", trim($list[5]));
				foreach ($files as $afile)
				{
					if (file_exists($atch."/".$afile))
					{
						$f = fopen($atch."/".$afile, "r"); $fnames .= (base64_encode(fgets($f))." "); fclose($f);
					}
				}
			}
			
			break;
		}
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
								<button type="button" class="btn btn-sm btn-success" style="padding-top: 2px; padding-bottom: 1px;" onclick="location.href = '<?php print($webp); ?>/make/';"><span class="glyphicon glyphicon-plus-sign" style="top: 2px;"></span> &nbsp; New</button>
								 &nbsp; 
								<select id="mailmark">
									<option name="m" id="<?php print($file); ?>">Mark Read / Unread</option>
									<option name="d" id="<?php print($file); ?>">Delete / Restore</option>
								</select>
								 &nbsp; 
								<button type="button" class="btn btn-sm btn-primary" style="padding-top: 2px; padding-bottom: 1px;" onclick="markmail(jQuery('#mailmark').find(':selected').attr('id'), jQuery('#mailmark').find(':selected').attr('name')); window.history.back();">Go</button>
							</th>
						</tr></table>
					</th></tr>
					<tr><th colspan="2" style="text-align: center; border: 0px;">
						<a href="javascript:window.location.href = '<?php print($webp); ?>/make/?e=' + jQuery('#from').text();">Reply</a>
						 &nbsp; 
						<a href="javascript:window.location.href = '<?php print($webp); ?>/make/?e=' + jQuery('#from').text().replace(user + '@' + dnsn, '') + ',' + jQuery('#dest').text().replace(user + '@' + dnsn, '');">Reply All</a>
						 &nbsp; 
						<a href="javascript:window.location.href = '<?php print($webp); ?>/make/?f=<?php print($file); ?>'">Forward</a>
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
